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

### JavaScript entries

Require SASS/CSS is simple as: 

```javascript
require('../scss/project.scss');
```

Use jQuery: 

```javascript
let $ = require('jquery');

// assign jQuery to a global for legacy modules
window.$ = window.jQuery = $;
```
> If you use jQuery in webpack, you can deactivate it in the contao page layout in order to avoid including it twice.


### Make encore bundle an optional dependency

If you create an reusable bundle and want to support setups that don't use encore, you need adjust the encore bundle confiuration:

1. Move your `huh_encore` configuration to an own config file, for example `config_encore.yml`.

1. In your `Plugin` class implement the `ExtensionPluginInterface` and merge the configs. Our [Utils Bundle](https://github.com/heimrichhannot/contao-utils-bundle) includes a method to do this for you. 

    ```php
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        return ContainerUtil::mergeConfigFile(
            'huh_encore',
            $extensionName,
            $extensionConfigs,
            __DIR__.'/../Resources/config/config_encore.yml'
        );
    }
    ```

1. Optional: Add encore bundle to your composer.json suggest section.

    ```json
    "suggest": {
        "heimrichhannot/contao-encore-bundle": "Symfony Webpack Encore integration for Contao.",
      }
    ``` 

### Add entries from your code (frontend module, content element,...)

Since version 1.3 it is possible to add encore entries from your code, so for example the slider assets are automatically included, if the slider module is added to the page. To do this, you can use the `huh.encore.asset.frontend` service.

Following example shows a backward compatible implementation: 

```php
if ($this->container->has('huh.encore.asset.frontend')) {
    $this->container->get('huh.encore.asset.frontend')->addActiveEntrypoint('contao-slick-bundle');
}
```

### Custom import templates

If you need custom templates for the import of javascript and stylesheet assets files, Encore Bundle provide support for this. 
Create a twig template (see `src/Resources/views` for examples) and register them in your (project) bundle config.

Example:

```yml
huh_encore:
  encore:
    templates:
      imports:
      - { name: default_css, template: "@HeimrichHannotContaoEncore/encore_css_imports.html.twig" }
      - { name: default_js, template: "@HeimrichHannotContaoEncore/encore_js_imports.html.twig" }
```

### Setup babel/corejs polyfill

To get your project working in older browser, you maybe want to use the babel polyfill. As of babel 7.4 `@babel/polyfill` [was deprecated in favor of corejs](https://babeljs.io/blog/2019/03/19/7.4.0) in favor of corejs. Here is our setup/migration guide.

1. Add corejs 3 and regenerator runtime to package.json. `npm i --save core-js regenerator-runtime` or: 

    ```json
    {
      "dependencies": {
        "core-js": "^3.1.4",
        "regenerator-runtime": "^0.13.2"
      }
    }
    ```

2. Update your webpack config: remove babel-poly entry and set corejs version in your babel config:

    ```js
    Encore
    // To be removed from webpack.config.js:
    .addEntry('babel-polyfill', [
            '@babel/polyfill'
        ])
     // Update babel config
    .configureBabel({},{
        useBuiltIns: 'entry',
        corejs: 3,
    })
    ```

3. Create an polyfill entry(for example `corejs-polyfill.js`) and just add these two imports:

    ```js
    import "core-js/stable";
    import "regenerator-runtime/runtime";
    ```
    
    Register the entry in your project bundle or in your webpack config. 
    
4. Update your yarn dependencies (`yarn install` or `composer update` (if you use foxy)) and compile. Set `corejs-polyfill` as active entry in your layout.
