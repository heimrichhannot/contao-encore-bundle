# Changelog

All notable changes to this project will be documented in this file.

## [2.2.0] - 2026-04-20
- Added: support for modern twig layouts of contao 5.7 ([#34](https://github.com/heimrichhannot/contao-encore-bundle/pull/34))
- Added: EntrypointsBuilder concept for retriving current page entrypoints ([#34](https://github.com/heimrichhannot/contao-encore-bundle/pull/34))
- Changed: add constant for default field name ([#34](https://github.com/heimrichhannot/contao-encore-bundle/pull/34))
- Fixed: removed dead or unnecessary code and checks ([#34](https://github.com/heimrichhannot/contao-encore-bundle/pull/34))
- Deprecated: `src/Asset/PageEntrypoints.php`, `src/Asset/TemplateAsset.php` and `src/Asset/TemplateAssetGenerator.php` ([#34](https://github.com/heimrichhannot/contao-encore-bundle/pull/34))
- Deprecated: `ConfigurationHelper::isEnabledOnPage()` ([#34](https://github.com/heimrichhannot/contao-encore-bundle/pull/34))
- Deprecated: getter-Methods in `EncoreEnabledEvent` ([#34](https://github.com/heimrichhannot/contao-encore-bundle/pull/34))

## [2.1.1] - 2026-03-30
- Fixed: compatibility issue with symfony 7

## [2.1.0] - 2025-02-07
- Changed: allow symfony 7 for better compatibility with contao 5.3 and 5.4
- Changed: some enhancements to code quality

## [2.0.0] - 2024-02-19
- Changed: utils bundle dependency version

## [2.0.0-beta2] - 2023-10-31
- Fixed: command registration error

## [2.0.0-beta] - 2023-10-31
- Added: support for contao 5
- Added: support for utils bundle v3
- Added: `EncoreEntriesSelectField` class
- Changed: dropped support for php <8.1
- Changed: droppend support for contao <4.13
- Changed: renamed Bundle class to `HeimrichHannotEncoreBundle`
- Changed: switch to new bundle structure
- Changed: encore entries select options is moved to layout legend in page settings
- Removed: deprecated addEncoreBabelPolyfill option
- Removed: support for adding encore entries from yaml
- Removed: support for adding encore to page template via template variables
- Removed: unused includes encore setup
- Removed: DcaGenerator class (use `EncoreEntriesSelectField` instead)
- Removed: all deprecated classes