# Theme entries review

## Self-review

Reviewed the complete patch against the approved activation, inheritance, migration and compatibility requirements. No unresolved correctness findings remain from this review.

Addressed findings:
- Entry field labels must respect theme translations and the existing custom-label API. Resolve labels through the table translations with the existing generic fallback.
- Doctrine schema representation must preserve the existing BLOB size rather than widening existing columns. Retain a 65535-byte capacity.
- Missing modern palettes must not instantiate the deprecated Contao exception hierarchy. Check palette existence before applying the extension (deprecated base class since Contao 4.7, removal announced for 5.0).

## Verification

- Full suite: 87 tests, 324 assertions each on Contao 4.13/PHP 8.2, Contao 5.3/PHP 8.2 and Contao 5.7/PHP 8.4.
- All runs use `SYMFONY_DEPRECATIONS_HELPER=max[total]=0`; no warnings are suppressed.
- Rendering integration exercises the real entry builder and Encore tag renderer for classic placeholders and the modern lazy response context. Contao 5.7 supplies the real LayoutEvent; older versions use the existing compatibility fixtures.
- Migration executes against SQLite, preserves entry data and explicit choices, tolerates missing tables/columns and is repeatable.
- PHPStan and ECS pass. Only changed production files were formatted.

## Requested external review

Claude Code CLI 2.1.325 was installed temporarily outside the repository. The read-only invocation used `--model claude-opus-5-5 --effort high --tools '' --no-session-persistence` and supplied the approved requirements plus the full patch as input.

The invocation returned `Not logged in · Please run /login` before making any API call. No external review or model-availability confirmation was obtained, and no substitute model was used. An authenticated CLI is required to complete this review. The user has been notified.
