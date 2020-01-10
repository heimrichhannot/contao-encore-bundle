# Changelog
All notable changes to this project will be documented in this file.

## [1.5.1] - 2020-01-10
- fixed a wrong key in PrepareCommand

## [1.5.0] - 2020-01-10
This release brings an overhaul to the bundle configuration (don't worry, we keep the bundle backward compatible). The biggest change is the deprecation of the encore config parameter (`huh_encore.encore`) as all children of that node are moving one level higher (`huh.js_entries` instead of `huh.encore.entries`). We also used that possibility to rename a few options to make them more clear or open possiblities for future development. But we left the `huh_encore.encore` (and marked it deprecated) key so no current config will be broken.

Following keys were renamed:
- `huh_encore.encore.entries` is now `huh_encore.js_entries`
- `huh_encore.encore.entries - requiresCss` is now `huh_encore.js_entries - requires_css`
- `huh_encore.encore.legacy` is now `huh_encore.unset_global_keys`
- all other config keys just lost the `.encore` part

Complete Changelog:

- configs can now be applied without encore config key (configs within encore key still work for bc reasons, but set deprecated now)
- added huh_encore.unset_jquery config to removed jquery from global assets array even if jquery is activated in layout section
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
- [POSSIBLE BC BREAK] moved main configurations from page root to layout (cause this is the please where it belongs)! While we provide a runonce that do some migration, we strongly recommend to check your configuration. Also no entries are moved to the layout (which issn't breaking, cause they still are loaded from page since entry overriding not changed.
- don't use global $objPage in generatePage hook

### Fixed
- possible error on fresh installation
- added missing utils bundle dependency


## [0.8.3] - 2019-04-23

### Changed
- register prepare command as service for symfony 4 compatibility (#3)

## [0.8.2] - 2019-04-12

### Fixed
- symfony 4 errors due non public services (#2)

## [0.8.1] - 2019-04-03

### Fixed
- symfony 4 error due dublicate service registration (#1)

## [0.8.0] - 2019-04-01

### Removed
- symfony framework as dependency

## [0.7.0] - 2019-03-19

### Added
- version 2 of `heimrichhannot/contao-multi-column-editor-bundle` as dependency

## [0.6.0] - 2019-02-22

### Added
- command line option `skip-entries` to explicitly skip the webpack generation of certain entry points (IMPORTANT: you have to rerun `vendor/bin/contao-console encore:prepare` if you already generated a `encore.bundles.js` file; see README.md for more detail)

## [0.5.0] - 2019-02-22

### Added
- command line option `entries` to limit the webpack generation to certain entry points (IMPORTANT: you have to rerun `vendor/bin/contao-console encore:prepare` if you already generated a `encore.bundles.js` file; see README.md for more detail)

## [0.4.4] - 2019-02-03

### Fixed
- added `exclude=true` to `tl_page.encoreEntries`

## [0.4.3] - 2018-12-20

### Changed
- enhanced and updated readme

## [0.4.2] - 2018-12-19

### Fixed
- ie babel polyfill no loading

## [0.4.1] - 2018-12-18

### Added
- webpack 0.22.* as foxy dependency

## [0.4.0] - 2018-12-17

### Added
- necessary changes for webpack 0.21+

## [0.3.0] - 2018-12-14

### Added
- symfony/webpack-encore-bundle as dependency

## [0.2.0] - 2018-10-30

### Added
- inline stylesheets (if needed)

## [0.1.2] - 2018-10-04

### Changed
- updated readme

## [0.1.1] - 2018-09-25

### Changed
- updated readme

## [0.1.0] - 2018-09-25

### Added
- english translation
- support for legacy TL_JQUERY

### Changed
- updated readme
