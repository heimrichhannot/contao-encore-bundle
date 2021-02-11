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

**Recommended:**

* In order to add the node dependencies required by composer bundles, you probably want to add them to your project's node dependencies when running webpack in the project's scope. You can use [Foxy](docs/introductions/bundles_with_webpack.md) for this task.


### Prepare your project and bundle

Setup your project for encore bundle: 

[Project setup](docs/setup_project.md)

[Bundle setup](docs/setup_bundle.md)


### Run Encore

1. Clear your cache
   
        php vendor/bin/contao-console cache:clear

1. Run encore prepare command

        php vendor/bin/contao-console encore:prepare

1.  Run encore to generate the assets

    `yarn encore dev`

1. Activate encore entries in the contao backend

Now run `yarn encore dev --watch` to generate the assets. For production assets (deployment), run `yarn encore production`.
  
    * If you have a large set of entries and the generation takes very long, you can use the command line parameter `--entries` in order to limit the generation to certain entries: `yarn encore dev --entries="entry1,entry2,entry3"` (the entry names can be taken from the generated file `encore.bundles.js`).
    * You can also explicitly skip certain entries for generation by using the command line parameter `--skip-entries`: `yarn encore dev --skip-entries="entry1,entry2,entry3"`.

1. If the generation succeeded without errors, you can now active encore entries. See Usage -> Activate encore entries for  how to do that.


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

        php vendor/bin/contao-console encore:prepare

The prepare command must be executed after every change to the encore entries configuration, e.g. after a composer update or changes to that configurations in your own code. 

This generates a file called `encore.bundles.js` in your project root.
This file contains entries for all contao encore compatible bundles that are added by calling `encoreBundles.addEntries();` in your `webpack.config.js`.

### Run encore

Run encore to generate/compile your assets. 

        yarn encore dev 
        yarn encore dev --watch 
        yarn encore prod

This bundle adds to additional options to the encore command:

* `entries`: Limit generation to passed entries. Useful if you have a large amount of entries and compilation needs time, but you're working on a specific entries.  Example: `yarn encore dev --entries="entry1,entry2,entry3"`
* `skip-entries`: Skip given entries for generation. Example: `yarn encore dev --skip-entries="entry1,entry2,entry3"`

## Documentation

[Project setup](docs/setup_project.md) - Prepare your contao project for use with encore and encore bundle

[Bundle setup](docs/setup_bundle.md) - Add encore bundle support to your bundle

[Setup Javascript](docs/setup_javascript.md) - Help about setting up your encore entries

[Developer Documentation](docs/developers.md)

[Configuration Reference](docs/configuration.md)