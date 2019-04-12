# Changelog
All notable changes to this project will be documented in this file.

## [0.8.2] - 2019-04-12

### Fixed
- symfony 4 errors due non public services

## [0.8.1] - 2019-04-03

### Fixed
- symfony 4 error due dublicate service registration

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
