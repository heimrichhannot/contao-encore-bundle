# Claude review: theme entries

Model: `claude-opus-5-5`; effort: `high`; CLI: 2.1.325. Reviewed head: `8f0e00e` against baseline `a449606`. Read-only review, 40 turns.

The two actionable P3 findings below were validated and fixed after this review. Each has a regression test observed failing before the fix and passing afterwards. Optional observations remain outside this change.

---

I found no high or medium priority bugs. Two low-priority bugs are worth fixing, and I checked both against the vendor code for Contao 4.13 and 5.7. I only read code; I didn't edit anything or run the test suites.

## Actionable bugs

### P3: The custom field label from `setFieldLabel()` is lost when the language file loads after the DCA
**Where:** `src/EventListener/DcaField/EncoreEntriesSelectFieldListener.php:26-28`

The listener now writes the custom label into `$GLOBALS['TL_LANG'][$table][$field]` instead of setting the field's `label`. Whether it survives depends on load order:

- **Contao 5.7:** `DefaultLabelsListener` is registered as `#[AsHook('loadDataContainer', priority: -255)]`, so it runs after this hook. It calls `System::loadLanguageFile($table)`. If that file hasn't been loaded yet and defines the field, it overwrites the custom label.
- **Contao 4.13:** the DCA loader adds a reference to `TL_LANG` as the label. When the language file is loaded later, its value wins.
- `Backend::getBackendModule()` loads the language first, so the normal edit view works. Other paths load the DCA first, for example `DcaExtractor` when the SQL cache is off (debug mode), pickers and parent-table lookups.

**Scenario:** a third-party bundle calls `EncoreEntriesSelectField::register('tl_news_archive')->setFieldLabel([...])` and also ships a `tl_news_archive.encoreEntries` translation. Its custom label shows in some requests and not in others, and dev and prod can differ. Before this change the custom label always won.

**Fix:** set the field's `'label'` only when a custom label is given. Otherwise leave it out, as now, and only fill in the fallback translation when none exists:
```php
if (null !== $customLabel = $options->getFieldLabel()) {
    $field['label'] = $customLabel;
} else {
    $GLOBALS['TL_LANG'][$table][$options->getFieldName()] ??= $this->getLabel('encoreEntriesSelect');
}
```
This keeps table translations ahead of the fallback, which was the goal of the change, and makes the custom label always win. A later language load still overwrites a written fallback, which is the intended result.

### P3: Theme entries are added even when the builder uses a different layout field
**Where:** `src/EntryPoint/EntryPointsBuilder.php:83-95`

`setLayout(?LayoutModel $layout, string $field)` lets callers read entries from a field other than `encoreEntries`. The theme merge ignores `$this->layoutField` and always adds `tl_theme.encoreEntries`.

**Scenario:** an integration builds a separate set of entries with `->setLayout($layout, 'encoreAmpEntries')`. After this change it also gets every theme entry and renders scripts and CSS it never selected. Callers can't turn this off: the developer docs ("add the sources you want") don't mention theme entries.

**Fix:** only merge theme entries when `EncoreEntriesSelectField::NAME_DEFAULT === $this->layoutField`. Alternatively, add a separate theme field setter.

## Optional observations (not bugs)

- **Unmigrated layouts show "Inherit" in the backend.** A layout still holding `''` (code deployed but migration not run yet, or imported from an older theme `.cto` archive) has no matching option, so the select shows the first one, "Inherit". The runtime still treats it as disabled, as approved. Saving the layout for any reason stores `i`. Running the migration later turns the remaining `''` rows into `0`, which then shows "Disable". The resolved behaviour is as approved, but what the editor sees doesn't match it. If this matters, you could show a backend warning for `''`.
- **Pre-existing, not from this patch:** `LayoutContainer::onLoadCallback` (`src/DataContainer/LayoutContainer.php:41`) calls `findByPk($dc->id)`. In the layout list, which sits under a theme, `$dc->id` is the theme's ID, so the check looks at an unrelated layout. It may show Encore messages that don't apply. Now that activation is inherited this may be more noticeable; worth a separate ticket.
- **Test hygiene:** `ThemeEncoreTest` and `EncoreEntriesSelectFieldListenerTest` register `tl_theme` and require core DCA files into globals without cleaning up afterwards. Results can depend on test order, but production code isn't affected.

## What I checked and found correct

- **Lookups:** `getRelated('pid')` works because `tl_layout.pid` is declared as a lazy `belongsTo` relation to `tl_theme` in both 4.13 and 5.7. A missing theme returns `null`.
- **Activation:** `isEnabledForLayout()` handles `i`/`1`/`0`, the legacy `''`, and booleans set in code (`true` → `'1'`).
- **Override order:** `EntryPoints::add()` replaces by entry name, so deactivating and reactivating works in the order theme → layout → parent pages → current page. Programmatic bags, the response context and `EncoreEnabledEvent` behave as before.
- **Migration:**
  - It is idempotent, and the result is the same whether the schema update runs before or after it.
  - The lowercase column key in `listTableColumns()` is correct for DBAL 3 and 4.
  - It is picked up automatically as a service.
- **SQL:** the BLOB with length 65535 still maps to `BLOB` on MySQL, so the column isn't widened. `CHAR(1) DEFAULT 'i'` only changes the default; existing rows stay as they are.
- **Select field:** the options `['i','1','0']`, the references and the saved `'0'` all work with Contao's select widget.
- **Palettes:** `config_legend` exists in `tl_theme` on both versions, and the `modern` palette check is correct.
- **Deprecated helpers:** `PageEntrypoints` and `TemplateAsset` were not extended, as approved.
