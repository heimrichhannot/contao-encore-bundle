# Theme entries review

## Self-review

Reviewed the complete patch against the approved activation, inheritance, migration and compatibility requirements. No unresolved correctness findings remain from this review.

Addressed findings:
- Entry field labels must respect theme translations and the existing custom-label API. Resolve labels through the table translations with the existing generic fallback.
- Doctrine schema representation must preserve the existing BLOB size rather than widening existing columns. Retain a 65535-byte capacity.
- Missing modern palettes must not instantiate the deprecated Contao exception hierarchy. Check palette existence before applying the extension (deprecated base class since Contao 4.7, removal announced for 5.0).

## Verification

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
