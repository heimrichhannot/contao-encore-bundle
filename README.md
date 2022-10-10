# Contao Encore Bundle
[![Latest Stable Version](https://img.shields.io/packagist/v/heimrichhannot/contao-encore-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-encore-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/heimrichhannot/contao-encore-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-encore-bundle)
![CI](https://github.com/heimrichhannot/contao-encore-bundle/workflows/CI/badge.svg)
[![Coverage Status](https://coveralls.io/repos/github/heimrichhannot/contao-encore-bundle/badge.svg?branch=master)](https://coveralls.io/github/heimrichhannot/contao-encore-bundle?branch=master)

Use the power and simplicity of symfony webpack encore in contao. This bundle let you decide on layout and page level, which encore entries should be loaded. If you want more, you can prepare your bundles define their own encore entries, so never need to manually add or remove encore entries again.

## Features
- use symfony encore ([symfony/webpack-encore](https://github.com/symfony/webpack-encore) and [symfony/webpack-encore-bundle](https://github.com/symfony/webpack-encore-bundle)) to enhance your contao assets workflow
- conditionally load your assets only if necessary (entrypoints can be activated in the backend in layout and page setting or added via service from your bundle code (e.g. in a frontend module))
- prepare your bundles to add encore entries when install them and strip assets from the contao global asset arrays


## Setup


### Prerequisites

* Read the [Encore Documentation](https://symfony.com/doc/current/frontend.html) in order to install Encore and understand the core concepts of Webpack and Symfony Encore.

### Prepare your project and bundle

Setup your project for encore bundle: 

[Project setup](docs/setup_project.md)

[Bundle setup](docs/setup_bundle.md)


### Run Encore

1. Run encore prepare command

       php vendor/bin/contao-console huh:encore:prepare [--skip-entries="entry1,entry2"]

1. If (yarn) dependencies have changed, run yan install

       yarn install

1. Run encore to generate the assets

       yarn encore dev

1. Activate encore entries in the contao backend (if not added from code)

## Usage

### Activate encore entries

1. In the contao backend, go to page layout configuration
1. Check "Activate Webpack Encore" and fill the mandatory fields
1. If you have a main project bundle entry containing the main stylesheets, add it as active entry, add also all other entries you want to have activated on every page. 
1. For page specific features, you can activate additional entries in page setting (site structure).
    * Be aware, that child pages will inherit settings from their parants
    * Pay attention that you check entries as active (if you want them to be loaded)!
    * If you want an already added entry to be not loaded on an specific page, select it as entry and don't check "active".

### Prepare command

    php vendor/bin/contao-console huh:encore:prepare

The prepare command must be executed after every change to the encore entries configuration, e.g. after a composer update or changes to that configurations in your own code. 

The command collect encore entries from all bundle and creates a file  called `encore.bundles.js` in your project root.
This file contains entries for all contao encore compatible bundles that are added by calling `encoreBundles.addEntries();` in your `webpack.config.js`.
If also collect the dependencies from the `package.json` files of bundles have EncoreExtensions registered.

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
