# Contao Encore Bundle

This bundle brings integration between symfony encore and contao. You can prepare your packages for encore workflow by defining your own webpack entries. You also have the option to strip legacy assets from the contao global array. In the contao backend you can configure to load packages on a **per page** level for having a great performance.

## Features

- use symfony encore ([symfony/webpack-encore](https://github.com/symfony/webpack-encore) and [symfony/webpack-encore-bundle](https://github.com/symfony/webpack-encore-bundle)) to enhance your contao assets workflow
- conditionally load your project assets only if necessary on a particular page (including page inheritance)
- asynchronously load dependency entries *on demand and cached* using webpack import() operator (see chapter "Dynamically importing common dependencies asynchronously")

## Setup 

### Prerequisites

* Read the [Encore Documentation](https://symfony.com/doc/current/frontend.html) in order to install Encore and understand the core concepts of Webpack and Symfony Encore.

**Recommended:**

* In order to add the node dependencies required by composer bundles, you probably want to add them to your project's node dependencies when running webpack in the project's scope. You can use [Foxy](docs/introductions/bundles_with_webpack.md) for this task.

### Project setup

**1\.** Install Encore bundle via composer 

```
composer require heimrichhannot/contao-encore-bundle
```

**2\.** Update your database

**3\.** Create your webpack/encore config file (`webpack.config.js`) in your project root.

Example:  

```javascript
let Encore = require('@symfony/webpack-encore'),
    encoreBundles = require('./encore.bundles');

Encore
    .setOutputPath('web/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications(true, (options) => {
        options.alwaysNotify = true;
    })
    .enableVersioning()

    // css
    .enableSassLoader()
    .enablePostCssLoader()

    // js
    .configureBabel(function(babelConfig) {
        // Add plugins here
    })
    .enableSourceMaps(!Encore.isProduction())
    
    .splitEntryChunks()
    .enableSingleRuntimeChunk()

    // babel polyfill e.g. for IE <= 11 Promise support (with Contao Encore Bundle this entry is added only if necessary, i.e. for IE <= 11)
    .addEntry('babel-polyfill', [
        '@babel/polyfill'
    ])
    
;

// this function adds entries for all contao encore compatible bundles automatically
// -> the source of that is the file "encore.bundles.js" in your project root which is
// generated automatically using the contao command "vendor/bin/contao-console encore:prepare"
// -> you can pass an array to the function if you want to skip certain entries
encoreBundles.addEntries();

// support dynamic chunks
let config = Encore.getWebpackConfig();

config.output.chunkFilename = '[name].bundle.js';

// support symlinks
config.resolve.symlinks = false;

module.exports = config;
```

_NOTE: Ignore possible warnings that the module `./encore.bundles` couldn't be found. We'll create this module in step 4 ;-)_

**4\.** In your `fe_page.html5` add the following in `<head>` region:

```<?= $this->encoreStylesheets; ?>```

and add the following into the footer region:

```<?= $this->encoreScripts; ?>```
   
This will add the necessary link and script tags automatically.
   
*NOTE: You can add javascript that should be explicitly loaded in the head region using `<?= $this->encoreHeadScripts; ?>`. Just add `head: true` to the entry in your yaml file. *
   
**5\.** If one of your webpack entries requires jQuery, you can deactivate jQuery in the Contao layout to don't include it twice.


### Bundle setup

In webpack an entry usually results in a JavaScript file generated in an output directory. These files can be included in the final HTML page.

**1\.** In order to make your entries visible to Contao Encore Bundle you have add them to the bundle configuration, typical `src/Resources/config/config.yml`. Full-featured example:

```yaml
huh_encore:
  encore:
    entries:
      - { name: contao-my-project-bundle, requiresCss: true, head: false, file: "vendor/acme/contao-my-project-bundle/src/Resources/public/js/my-project-bundle.js" }
      - { name: special-feature, requiresCss: true, head: false, file: "vendor/acme/contao-my-project-bundle/src/Resources/public/js/awesome-but-rare-used-feature.js" }
    legacy:
      js:
        - contao-my-project-bundle
        - some-other-dependency
      jquery:
        - my-jquery-dependency
      css:
        - contao-my-project-bundle
```

Explanation:
* Within `entries` you register Javascript files, which can be activated from the Contao backend
    * you can register multiple entries per bundle, so you don't need to include all files/features in every page
    * `name`: Will be shown in contao backend and will be used as alias/identifier in the database. Required.
    * `file`: Path to the Javascript file. Required
    * `requireCss`: Set to true, if entry requires css.
    * `head`: Set to true, if entry should added to the `encoreHeadScripts` section (see project setup) in your page layout instead to the bottom (CSS will always be added to the head).
* Within `legacy` you can define assets, that will be stripped from the global contao arrays. Here you can add assets, that you serve with webpack, so they won't be loaded twice or on the wrong page. IMPORTANT: The strings defined here must match the array keys in Contao's global arrays
    * `js`: Assets will be stripped from `$GLOBALS['TL_JAVASCRIPT']`
    * `jquery`: Assets will be stripped from `$GLOBALS['TL_JQUERY']`
    * `css`: Assets will be stripped from `$GLOBALS['TL_USER_CSS']` and `$GLOBALS['TL_CSS']`

**2\.** If your config isn't already registered in your Contao Manager `Plugin` class, you need to do this now: implement the `ConfigPluginInterface` class and register the config in the new `registerContainerConfiguration` method:

```php
class Plugin implements BundlePluginInterface, ConfigPluginInterface
{
    //...

    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        // The bundle id ("VendorProjectBundle" in this example) is typically your bundle class name
        $loader->load('@VendorProjectBundle/Resources/config/config.yml');
    }
}
```

**3\.** Add encore bundle to your composer.json file (See Project setup step 1).

> If you want encore bundle to be an optional dependency, please see "Usage -> Make encore bundle an optional dependency"

**4\.** You probably want to have your bundle's node dependencies added automatically to the project's node_modules directory when installed. You can simply use [Foxy](https://github.com/fxpio/foxy) for this task. To keep it simple: besides having foxy installed in your project, you need to set `"foxy": true` in the `extra` section of your bundle's `composer.json` and add an ordinary `package.json` as usual for node modules. See [heimrichhannot/contao-list-bundle](https://github.com/heimrichhannot/contao-list-bundle) for an example.


### Run Encore

**1\.** Clear your cache (`vendor/bin/contao-console cache:clear`)

**2\.** Run the Contao command `vendor/bin/contao-console encore:prepare`. This generates a file called `encore.bundles.js` in your project root.
This file contains entries for all contao encore compatible bundles that are added by calling `encoreBundles.addEntries();` in your `webpack.config.js`.

_IMPORTANT: You have to call this command everytime you want your webpack entries to be updated, e.g. if you added new entries to your yml configuration or removed some._

**3\.** Now run `yarn encore dev --watch` to generate the final CSS. If you like to generate the production mode css, run `yarn encore production`

**3\.1\.** If you have a large set of entries and the generation takes very long, you can use the command line parameter `--entries` in order to limit the generation to certain entries: `yarn encore dev --entries="entry1,entry2,entry3"` (the entry names can be taken from the generated file `encore.bundles.js`).

**4\.** If the generation succeeded without errors, you can now active encore entries. See Usage -> Activate encore entries for  how to do that.



## Usage

### Activate encore entries

* Go in the contao backend to site structure and choose the website root
* Scroll to encore settings and check "Activate Webpack Encore"
* fill in the (mandatory) fields
* if you have a main project bundle entry containing the main stylesheets, add it as active entry, add also all other entries you want to have activated on every page. Pay attention that you check all entries as active (if you want them to be loaded)!
* for page specific features, go to this page settings and add it as active entry. NOTICE: if you active an entry on an page with subpages, they will inherit the settings from their parents. You can deactivate an inherit entry by adding it and don't check "active".

### JavaScript entires

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

**1\.** Move your `huh_encore` configuration to an own config file, for example `config_encore.yml`.

**2\.** In your `Plugin` class implement the `ExtensionPluginInterface` and merge the configs. Our [Utils Bundle](https://github.com/heimrichhannot/contao-utils-bundle) includes a method to do this for you. 

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

**3\.** Optional: Add encore bundle to your composer.json suggest section.

```json
"suggest": {
    "heimrichhannot/contao-encore-bundle": "Symfony Webpack Encore integration for Contao.",
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