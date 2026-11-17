# ![waypoints.svg](docs/img/waypoints.svg) Contao Encore Bundle
[![Latest Stable Version](https://img.shields.io/packagist/v/heimrichhannot/contao-encore-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-encore-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/heimrichhannot/contao-encore-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-encore-bundle)
![CI](https://github.com/heimrichhannot/contao-encore-bundle/workflows/CI/badge.svg)
[![Coverage Status](https://coveralls.io/repos/github/heimrichhannot/contao-encore-bundle/badge.svg?branch=v2)](https://coveralls.io/github/heimrichhannot/contao-encore-bundle?branch=v2)
![](https://img.shields.io/badge/PHPStan-level%205-brightgreen.svg?style=flat)

Use the power and simplicity of symfony webpack encore in contao. This bundle let you decide on theme, layout and page level, which encore entries should be loaded. If you want more, you can prepare your bundles define their own encore entries, so never need to manually add or remove encore entries again.

## Features
- use symfony encore ([symfony/webpack-encore](https://github.com/symfony/webpack-encore) and [symfony/webpack-encore-bundle](https://github.com/symfony/webpack-encore-bundle)) to enhance your contao assets workflow
- conditionally load your assets only if necessary (entrypoints can be activated in the backend in layout and page setting or added from your bundle code (e.g. in a frontend module))
- prepare your bundles to add encore entries when install them and strip assets from the contao global asset arrays


## Setup


### Prerequisites

* Read the [Encore Documentation](https://symfony.com/doc/current/frontend.html) in order to install Encore and understand the core concepts of Webpack and Symfony Encore.

### Prepare your project and bundle

Setup your project for encore bundle: 

:arrow_right:	[Project setup](docs/setup_project.md)

:arrow_right:	[Bundle setup](docs/setup_bundle.md)


### Run Encore

1. Run encore prepare command

       php vendor/bin/contao-console huh:encore:prepare

1. If (yarn) dependencies have changed, run yan install

       yarn install

1. Run encore to generate the assets

       yarn encore dev

1. Activate encore entries in the contao backend (if not added from code)

## Usage

### Activate encore entries

1. In the Contao backend, edit a theme and check "Activate Webpack Encore".
1. Select the entries that all layouts of this theme should inherit.
   > You can add additional entries or disable inherited entries layout or page level. It is also possible to disable encore completely for a specific layout.
   
Entries are resolved in this order: **theme → layout → parent pages → current page**. The more specific setting wins. If Encore is enabled explicitly in a layout, it also receives the theme's entries even when the theme's activation checkbox is unchecked.

### Prepare command

    php vendor/bin/contao-console huh:encore:prepare

The prepare command must be executed after every change to the encore entries configuration, e.g. after a composer update or changes to that configurations in your own code. 

The command collect encore entries from all bundle and creates a file  called `encore.bundles.js` in your project root.
This file contains entries for all contao encore compatible bundles that are added by calling `encoreBundles.addEntries();` in your `webpack.config.js`.

It also collects the dependencies from the `package.json` files of bundles have EncoreExtensions registered and adds them to the project dependencies.

```
Description:
  Does the necessary preparation for contao encore bundle. Needs to be called after changes to bundle encore entries.

Usage:
  huh:encore:prepare [options]
  encore:prepare

Options:
      --skip-entries[=SKIP-ENTRIES]  Add a comma separated list of entries to skip their generation. [default: false]
```

### Run encore

Run encore to generate/compile your assets. 

    yarn encore dev 
    yarn encore dev --watch 
    yarn encore prod

## Documentation

[Project setup](docs/setup_project.md) - Prepare your contao project for use with encore and encore bundle

[Bundle setup](docs/setup_bundle.md) - Add encore bundle support to your bundle

[Setup Javascript](docs/setup_javascript.md) - Help about setting up your encore entries

[Developer Documentation](docs/developers.md)

[Configuration Reference](docs/configuration.md)
