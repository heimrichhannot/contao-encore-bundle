# Theme entries review

## Self-review

Reviewed the complete patch against the approved activation, inheritance, migration and compatibility requirements. No unresolved correctness findings remain from this review.

Addressed findings:
- Entry field labels must respect theme translations and the existing custom-label API. Resolve labels through the table translations with the existing generic fallback.
- Doctrine schema representation must preserve the existing BLOB size rather than widening existing columns. Retain a 65535-byte capacity.
- Missing modern palettes must not instantiate the deprecated Contao exception hierarchy. Check palette existence before applying the extension (deprecated base class since Contao 4.7, removal announced for 5.0).

## Verification before rebase

- Full suite: 89 tests, 326 assertions each on Contao 4.13/PHP 8.2, Contao 5.3/PHP 8.2 and Contao 5.7/PHP 8.4.
- All runs use `SYMFONY_DEPRECATIONS_HELPER=max[total]=0`; no warnings are suppressed.
- Rendering integration exercises the real entry builder and Encore tag renderer for classic placeholders and the modern lazy response context. Contao 5.7 supplies the real LayoutEvent; older versions use the existing compatibility fixtures.
- Migration executes against SQLite, preserves entry data and explicit choices, tolerates missing tables/columns and is repeatable.
- PHPStan and ECS pass. Only changed production files were formatted.

## External review completed

The globally installed Claude Code CLI 2.1.325 completed the read-only review with `claude-opus-5-5` and effort `high`. The result confirms that exact model; no substitute was used. The earlier unauthenticated attempt made no API calls.

Claude reported no high- or medium-priority defects and two actionable P3 findings. Both were independently checked and fixed:

- Explicit labels configured through `setFieldLabel()` could be overwritten by language files loaded after the DCA. Preserve an explicitly requested custom label in the field definition; ordinary fields continue to use automatic table-label resolution. A regression test simulates later language loading.
- A builder reading a custom layout field also collected the default theme entries. Merge default theme entries only when the layout field is `encoreEntries`, preserving isolated custom collections. A regression test verifies the resulting collection.

Both regression tests failed before the fixes. The final complete suites pass with 89 tests and 326 assertions on each supported test environment, without deprecations; PHPStan and ECS also pass.

The full independent review is saved in [claude-theme-entries-review.md](claude-theme-entries-review.md). Optional notes about unmigrated imports, a pre-existing backend warning lookup and test global-state cleanup are documented there and were not included as unrelated production changes.

## Verification after rebase onto v2 `8a18470`

- All three feature commits were rebased; the production feature changes and Claude finding fixes are preserved.
- Retained the new v2 dependency constraints, stricter deprecation configuration, dependency-injection tests, real-translator tests and simplified test bootstrap unchanged.
- Resolved the overlapping changelog and test edits without removing upstream or feature regression coverage.
- Full suite: 96 tests / 345 assertions per run on Contao 5.3.54 with PHP 8.2–8.5, and Contao 5.7.17 with PHP 8.5. No deprecations.
- PHPStan, ECS and Composer validation pass. Self-review of the range-diff and the combined source found no additional correctness issues. The external review above predates the rebase; no feature production logic changed during rebasing.

## Conditional field visibility follow-up

Theme entries now use the native checkbox subpalette. Layout entries and import-template fields use the native enable subpalette; an attributed palette callback adds them when inheriting from an enabled theme. The callback reads submitted selectors (including bulk-edit ids) before saved values. No write operation or frontend activation behavior changed.

Regression tests exercise the real DC_Table palette resolution on Contao 5.3 and the real PaletteBuilder service on Contao 5.7, covering disabled/enabled themes, inheritance, explicit overrides, missing themes, immediate selector changes and bulk editing. They failed before implementation. Test fixtures were adjusted to supply the real Contao 5.7 service and its explicit record lookup parameters.

Final validation: 110 tests / 397 assertions per environment, no deprecations, on Contao 5.3/PHP 8.2–8.5 and Contao 5.7/PHP 8.5. PHPStan and ECS pass. Self-review found no outstanding issue. Claude Opus 5.5/high returned no actionable findings; see [claude-visibility-review.md](claude-visibility-review.md). Its source inspection covered 5.3; the implementer verified 5.7 directly.
