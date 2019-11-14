# Contao Encore Bundle
[![Latest Stable Version](https://img.shields.io/packagist/v/heimrichhannot/contao-encore-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-encore-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/heimrichhannot/contao-encore-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-encore-bundle)
[![Build Status](https://travis-ci.org/heimrichhannot/contao-encore-bundle.svg?branch=master)](https://travis-ci.org/heimrichhannot/contao-encore-bundle)
[![Coverage Status](https://coveralls.io/repos/github/heimrichhannot/contao-encore-bundle/badge.svg?branch=master)](https://coveralls.io/github/heimrichhannot/contao-encore-bundle?branch=master)

This bundle brings deep integration for symfony encore into contao. On the one hand, your can prepare your bundles to define own webpack entries, which added with just one command to your webpack entries. On the other hand, this bundle allows you to add encore entries only on the pages  you need them for optimizing your website performance.

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

1. Clear your cache (`vendor/bin/contao-console cache:clear`)

1. Run the Contao command `vendor/bin/contao-console encore:prepare`. This generates a file called `encore.bundles.js` in your project root.
This file contains entries for all contao encore compatible bundles that are added by calling `encoreBundles.addEntries();` in your `webpack.config.js`.

    > IMPORTANT: You have to call this command every time you want your bundle webpack entries to be updated, e.g. if you added new entries to your yml configuration or added a new encore-bundle compatible bundle.

1.  Now run `yarn encore dev --watch` to generate the assets. For production assets (deployment), run `yarn encore production`.
  
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

## Develops 

[Setup Javascript](docs/setup_javascript.md) - Help about setting up your encore entires. 

[Developer Documentation](docs/developers.md)