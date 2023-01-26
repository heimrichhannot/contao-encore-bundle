# Changelog

All notable changes to this project will be documented in this file.

## [1.18.0] - 2023-01-26
- Added: [EncoreEnabledEvent](docs/developers.md#events) ([#26])
- Changed: PageModel is now loaded from request instead from globals ([#26])
- Changed: less dependency on utils bundle ([#25])
- Deprecated: EntryChoice ([#25])

## [1.17.4] - 2022-12-21
- Fixed: encore not applied on some error page cases

## [1.17.3] - 2022-11-17
- Fixed: assets not applied on error pages

## [1.17.2] - 2022-10-12
- Changed: Add phpstan to ci pipeline with level 1 ([#22])

## [1.17.1] - 2022-10-11
- Fixed: compatibility with contao 4.9

## [1.17.0] - 2022-10-11
- Added: support for composer extra.public_folder setting (public folder)

## [1.16.1] - 2022-10-11
- Changed: enhances command output
- Fixed: bundle path not correct for non-symlinked bundle

## [1.16.0] - 2022-10-10
- Added: implemented and support [Contao Encore Contracts](https://github.com/heimrichhannot/contao-encore-contracts) ([#21])
- Added: yarn dependencies now collected by prepare command for bundles using Encore Contracts, so [foxy](https://github.com/fxpio/foxy) is no more recommended (and can hopefully be dropped soon) ([#21])
- Changed: a lot of internal refactoring ([#21])
- Changed: minimum supported php version is now 7.4
- Deprecated: usages of yaml for registering entrypoints  ([#21])

## [1.15.1] - 2022-03-21
- Fixed: make use_contao_template_variables work with contao 4.13

## [1.15.0] - 2022-02-09

- Changed: minimum contao version is now 4.9
- Changed: supported symfony versions are `^4.4||^5.4`

## [1.14.0] - 2022-01-10
- Changed: hooks not using service annotations
- Changed: some refactoring and cleanup
- Changed updated test setup
- Fixed: changes in contao 4.9 leading to warning/non working asset insertion if encore bundle template variables are used


## [1.13.0] - 2021-09-28
- Changed: refactored runonce to Migration
- Changed: minimum contao version is now 4.9

## [1.12.1] - 2021-08-27

- Added: php8 support

## [1.12.0] - 2021-07-28

- removed `skip-entries` and `entries` from js implementation because not supported by webpack anymore
- added `skip-entries` to `encore:prepare` command

## [1.11.2] - 2021-07-06

- fixed symfony 5 issue
- fixed missing dependencies
- some small code enhancements

## [1.11.1] - 2021-05-06

- fixed vendor dependency issue if non-head scripts were loaded after the head scripts while the head scripts contained
  jquery (solution: head scripts need to be passed to `encore_entry_script_tags` twig filter first so that the vendor
  entries are in the head always)

## [1.11.0] - 2021-04-12

- added ConfigurationHelper class
- refactored ReplaceDynamicScriptTagsListener to use ConfigurationHelper

## [1.10.0] - 2021-03-08

This release enhances the possibilities to include encore in custom routes. See developer documentation for more
information.

- added DcaGenerator class
- added EntrypointCollectionFactory and EntrypointCollection class
- added TemplateAssetGenerator class

## [1.9.2] - 2021-03-03

- fixed ordering issue with use_contao_template_variables
- added notice to layout settings if use_contao_template_variables used together with "Add jQuery"
- updated tests

## [1.9.1] - 2021-02-22

- fixed issue with FragmentControllers

## [1.9.0] - 2021-02-11

- Add script tag support (#16)
- added use_contao_template_variables option (#16)
- updated documentation (#16)

## [1.8.5] - 2021-02-01

- fixed encore assets not correctly added to all contao error pages

## [1.8.4] - 2020-11-06

- fixed wrong key used in legacy config merge

## [1.8.3] - 2020-09-02

- fixed entries filtered when encore disabled in layout

## [1.8.2] - 2020-09-02

- updated tests

## [1.8.1] - 2020-09-02

- small code enhancement

## [1.8.0] - 2020-09-01

- added EntryHelper::cleanGlobalArrays() as public endpoint to clean global assets
- added ReplaceDynamicScriptTagsListener where global is now cleaned since generatePage hook is triggered to early in
  some circumstances
- fixed global contao asset array not always correctly cleaned
- fixed HookListener::cleanGlobalArrays()

## [1.7.0] - 2020-06-16

- added some hints to error messages for easier debugging

## [1.6.3] - 2020-06-05

- fixed modification of dca palettes

## [1.6.2] - 2020-06-04

- fixed non public frontendasset service alias

## [1.6.1] - 2020-01-13

- updated composer meta data

## [1.6.0] - 2020-01-13

With this release we fixed a long overseen bug that the asset order set in the contao backend was not respected when
adding assets to the page.   
**This may lead to ordering issues if your code relies on the order of insertion.**

- the order set in contao backend when adding encore entries is now respected when including the assets to the page
- refactored HookListener to GeneratePageListener class (HookListener remains as proxy class for backward compatibility)

## [1.5.3] - 2020-01-13

- fixed global array not cleaned correct

## [1.5.2] - 2020-01-10

- fixed a spelling issue in all entries fields

## [1.5.1] - 2020-01-10

- fixed a wrong key in PrepareCommand

## [1.5.0] - 2020-01-10

This release brings an overhaul to the bundle configuration (don't worry, we keep the bundle backward compatible). The
biggest change is the deprecation of the encore config parameter (`huh_encore.encore`) as all children of that node are
moving one level higher (`huh.js_entries` instead of `huh.encore.entries`). We also used that possibility to rename a
few options to make them more clear or open possiblities for future development. But we left the `huh_encore.encore` (
and marked it deprecated) key so no current config will be broken.

Following keys were renamed:

- `huh_encore.encore.entries` is now `huh_encore.js_entries`
- `huh_encore.encore.entries - requiresCss` is now `huh_encore.js_entries - requires_css`
- `huh_encore.encore.legacy` is now `huh_encore.unset_global_keys`
- all other config keys just lost the `.encore` part

Complete Changelog:

- configs can now be applied without encore config key (configs within encore key still work for bc reasons, but set
  deprecated now)
- added huh_encore.unset_jquery config to removed jquery from global assets array even if jquery is activated in layout
  section
- renamed some config keys and parameters
- refactored config parameter calls and autowiring of some classes

## [1.4.1] - 2019-12-12

- add missing public service alias for TemplateAsset

## [1.4.0] - 2019-12-09

- refactored template variable generation into own class

## [1.3.2] - 2019-12-03

- fixed typo in prepare command

## [1.3.1] - 2019-11-14

- fixed dynamic added entries could no be deactivated by setting inactive on a page
- fixed backward incompatible method rename in version 1.3.0
- fixed possible autowiring issue for symfony 4

## [1.3.0] - 2019-10-16

- added service for adding encore entrypoints from code
- added fe_page_encore_bundle template for out of the box usage or demonstration
- global array now also cleaned in generatePage hook
- fixed babel polyfill not loaded when used with addBabelPolyfill checkbox

## [1.2.2] - 2019-07-09

### Fixed

- script error in head

## [1.2.1] - 2019-07-02

### Changed

- updated readme and package.json

## [1.2.0] - 2019-07-02

### Changed

- removed the ie babel polyfill filter due errors
- marked addEncoreBabelPolyfill as deprecated

### Fixed

- babel-polyfill issue in some configurations
- translations

## [1.1.4] - 2019-06-28

### Fixed

- modernizr issue
- babel-polyfill issue

## [1.1.3] - 2019-05-21

### Changed

- added notice about current environment to prepare command
- don't set webpack cache as set inactive in dev environment not worked. Must be set to true in project config.

### Fixed

- command cache parameter type

## [1.1.2] - 2019-05-21

### Changed

- reset Webpack Encore Bundle cache on prepare command (fixes asset versioning issues)

## [1.1.1] - 2019-05-20

### Fixed

- wrong tree builder root node

## [1.1.0] - 2019-05-17

### Added

- support for entrypoints in the entrypoints.json (#6, #8)

## [1.0.3] - 2019-05-16

### Fixed

- removed encorePublicPath field as it is not used anymore (#7)

## [1.0.2] - 2019-05-15

### Fixed

- more missing variable declarations (#5)

## [1.0.1] - 2019-05-15

### Fixed

- missing variable declaration in generated encore.bundles.js code (#5)

## [1.0.0] - 2019-05-15

### Changed

- [POSSIBLE BC BREAK] moved main configurations from page root to layout (cause this is the please where it belongs)!
  While we provide a runonce that do some migration, we strongly recommend to check your configuration. Also no entries
  are moved to the layout (which issn't breaking, cause they still are loaded from page since entry overriding not
  changed.
- don't use global $objPage in generatePage hook

### Fixed

- possible error on fresh installation
- added missing utils bundle dependency

[#26]: https://github.com/heimrichhannot/contao-encore-bundle/pull/26
[#25]: https://github.com/heimrichhannot/contao-encore-bundle/pull/25
[#22]: https://github.com/heimrichhannot/contao-encore-bundle/pull/22
[#21]: https://github.com/heimrichhannot/contao-encore-bundle/pull/21