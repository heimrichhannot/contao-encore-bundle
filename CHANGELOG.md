# Changelog
All notable changes to this project will be documented in this file.

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
