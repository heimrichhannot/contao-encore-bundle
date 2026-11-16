# Theme entries implementation

Approved design: Theme checkbox enables Encore; theme entries have no per-entry active flag. Layout addEncore selects i (inherit, new default), 1 (enable), 0 (disable). Layout and page entries override inherited entries. Existing empty layout flags migrate to 0; 1 remains unchanged. Themes start disabled.

- [x] Test and implement layout activation and theme entry inheritance.
- [x] Test and implement DCA, translations and idempotent migration.
- [x] Verify rendered output with the real Encore renderer; run PHPUnit, PHPStan and ECS.
- [x] Self-review and fix valid findings.
- [x] Claude Opus 5.5 high CLI review completed with the global authenticated CLI; both validated P3 findings fixed with failing-then-passing regression tests.
- [x] Commit, push feature/theme-entries and create GitHub PR against v2: https://github.com/heimrichhannot/contao-encore-bundle/pull/41 (external review completed; ready for review).

Constraints: PHP >=8.1, Contao 4.13 and 5; inspect vendor APIs before use. Preserve response-context/custom-bag priority and request/event behavior. Do not extend deprecated asset helpers. Test missing themes/layouts, cross-theme isolation, all activation combinations, migration repeatability and real CSS/JS output.

Workspace: .worktrees/theme-entries, baseline a449606. Execution is inline as requested. No changes in the original checkout.

Rulings:
- Use the layout's existing lazy pid relation to retrieve its theme; no additional factory or service dependency is necessary. The vendor getRelated PHPDoc incorrectly returns the calling model type, so local ThemeModel annotations document the real DCA relation.
- Contao 4.13 includes language files directly and does not support setting contao_* catalogues via Symfony (MessageCatalogue::add/replace throw). New Contao language files therefore use the compatible global format.
- Preserve BLOB capacity (65535 bytes) when converting the shared entry field to Doctrine schema representation.
- Baseline: 55 tests, one stale array-based entry-options fixture error, ten deprecated isEnabledOnCurrentPage calls. Update these affected tests to the current object contract and isEnabledOnPage API; do not suppress deprecations.
- Avoid the pre-existing missing-modern-palette exception path: its Contao 4.13 inheritance loads a deprecated class. Check palette existence instead.

Verification so far:
- PHPUnit with strict deprecation handling: Contao 4.13/PHP 8.2 and Contao 5.3/PHP 8.2, each 83 tests / 320 assertions at that stage, zero errors or deprecations.
- PHPStan: no errors; ECS: clean after formatting only changed production files.
- A separate temporary PHP container is used after the original development container was stopped.
- Claude CLI 2.1.325 installed under /tmp/encore-claude-review. Initially loggedIn=false; the user subsequently installed and authenticated the global CLI. No model substitution.

Self-review findings addressed:
- Generated entry field labels previously overwrote table translations. Let Contao resolve the main field label, retaining custom option labels and fallback translations.
- Doctrine BLOB representation without length would enlarge the old BLOB to LONGBLOB. Preserve the original capacity explicitly.
- Rendering fixtures must preserve Model static methods for registry lookup and use controlled model data independently of the database schema; avoid mocking the builder or renderer.

Final verification: Contao 4.13/PHP 8.2, Contao 5.3/PHP 8.2 and Contao 5.7/PHP 8.4 each pass 89 tests / 326 assertions with SYMFONY_DEPRECATIONS_HELPER=max[total]=0. PHPStan and ECS pass. The modern integration uses the actual Contao 5.7 LayoutEvent and lazy response context.

External review fixes: explicit custom labels survive later language loading; builders using a custom layout field do not inherit default theme entries. Full external report and finding dispositions are saved alongside this plan.
