# Contao Encore Bundle

This bundle offers the opportunity to specify which webpack entries (JavaScript, CSS, i.e. whatever webpack supports) should be loaded on a per page level. 3rd party bundles are also supported if they're made compatible.

## Features

- conditionally load your project assets only if necessary on a particular page
- asynchronously load shared dependency entries *on demand and cached* using webpack import() operator (see chapter "Dynamically importing common dependencies asynchronously")

## Installation

1. Install via composer: `composer require heimrichhannot/contao-encore-bundle` and update your database.
2. Read https://symfony.com/doc/current/frontend.html in order to understand the core concepts of webpack and symfony encore.
3. Now you can enable encore for a particular page root in the Contao backend and specify various options.
4. For pages with activated encore support you can then specify which webpack entries should be loaded on this page and its sub pages (inheritance is supported).<br>
   *NOTE: In order to see entries here, see chapter "Define webpack entries for Contao Encore Bundle"*
5. In your `fe_page.html5` add the following in `<head>` region:<br>
   `<?= $this->encoreStylesheets; ?>` <br>
   and add the following into the footer region:<br>
   `<?= $this->encoreScripts; ?>` 
   This will add the necessary link and script tags automatically.
6. If one of your webpack entries requires jQuery, of course you can deactivate jQuery in the Contao layout now.

## Configuration

### Define webpack entries for Contao Encore Bundle in your project

In webpack an entry usually results in a JavaScript file generated in an output directory. These files are included in the final HTML page.

Please do the following steps:

1\. At first webpack entries must be specified in your `webpack.config.js` (as normally with webpack). Here's how this file could look like:

```javascript
let Encore = require('@symfony/webpack-encore'),
    encoreBundles = require('./encore.bundles');

Encore
    .setOutputPath('web/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableVersioning()

    // css
    .enableSassLoader()
    .enablePostCssLoader()

    // js
    // needed for dynamic imports
    .configureBabel(function(babelConfig) {
        babelConfig.plugins.push('syntax-dynamic-import');
    })
    .enableSourceMaps(!Encore.isProduction())
    .createSharedEntry('vendor', [
        'jquery',
        'bootstrap'
    ])

    // babel polyfill e.g. for IE <= 11 Promise support (with Contao Encore Bundle this entry is added only if necessary, i.e. for IE <= 11)
    .addEntry('babel-polyfill', [
        'babel-polyfill'
    ])
;

// this function adds entries for all contao encore compatible bundles automatically
// -> the source of that is the file "encore.bundles.js" in your project root which is
// generated automatically using the contao command "vendor/bin/contao-console encore:prepare"
// -> you can pass an array to the function if you want to skip certain entries
encoreBundles.addEntries([
    'my-skipped-bundle'
]);

// support dynamic chunks
let config = Encore.getWebpackConfig();

config.output.chunkFilename = '[name].bundle.js';

// support symlinks
config.resolve.symlinks = false;

module.exports = config;
```

_NOTE: Ignore possible warnings that the module `./encore.bundles` couldn't be found. We'll create this module in step 4 ;-)_

2\. Now in order to make your entries visible to Contao Encore Bundle you have to create a `config_encore.yml` file (or whatever name you like) in your project bundle containing the following structure:

```yaml
huh:
    encore:
        entries:
            - { name: contao-my-project-bundle, file: "vendor/acme/contao-my-project-bundle/src/Resources/public/js/my-project-bundle.js", requiresCss: true }
        legacy:
            # Assets defined here are stripped from Contao's global arrays automatically (e.g. $GLOBALS['TL_JAVASCRIPT']) since they're not needed there if your assets are served through webpack
            # IMPORTANT: The strings defined here must match the array keys in Contao's global arrays
            js:
                - contao-my-project-bundle
                - some-other-dependency
            css:
                - contao-my-project-bundle
```

*NOTE: If your entry doesn't require any css, set `requiresCss` to `false`, of course*

An example of `my-project-bundle.js` would be:

```javascript
// only if you need jQuery
let $ = require('jquery');

// assign jQuery to a global for legacy modules
window.$ = window.jQuery = $;

// require your styleshets if needed
require('../scss/project.scss');

$(document).ready(function() {

});
```

3\. The next step is to merge your config with the default one of Contao Encore Bundle. For this adjust your bundle's `Plugin.php` by implementing `Contao\ManagerPlugin\Config\ExtensionPluginInterface` and adding the code in `getExtensionConfig()`:

```php
class Plugin implements BundlePluginInterface, ExtensionPluginInterface
{
    // ...
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container) {
        return ContainerUtil::mergeConfigFile(
            'huh_encore',
            $extensionName,
            $extensionConfigs,
            $container->getParameter('kernel.project_dir').'/vendor/acme/contao-my-project-bundle/src/Resources/config/config_encore.yml'
        );
    }
}
```

4\. Now run the Contao command `vendor/bin/contao-console encore:prepare`. This generates a file called `encore.bundles.js` in your project root.
    This file contains entries for all contao encore compatible bundles that are added by calling `encoreBundles.addEntries();` in your `webpack.config.js`.<br><br>
    _IMPORTANT: You have to call this command everytime you want your webpack entries to be updated, e.g. if you added new entries to your yml configuration or removed some._

### Preparing a library bundle for Contao Encore Bundle

Basically preparing a bundle acting as a library bundle for your projects is similar to preparing the project's main bundle. Do the following steps:

1\. In order to make your entries visible to Contao Encore Bundle you have to create a `config_encore.yml` file (or whatever name you like) in your bundle containing the following structure:

```yaml
huh:
    encore:
        entries:
            - { name: contao-my-lib-bundle, file: "vendor/acme/contao-my-lib-bundle/src/Resources/public/js/jquery.my-lib-bundle.js" }
        legacy:
            # Assets defined here are stripped from Contao's global arrays automatically (e.g. $GLOBALS['TL_JAVASCRIPT']) since they're not needed there if your assets are served through webpack
            # IMPORTANT: The strings defined here must match the array keys in Contao's global arrays
            js:
                - contao-lib-bundle
            css:
                - contao-lib-bundle
                - some-other-dependency
```

*NOTE: If your entry doesn't require any css, set `requiresCss` to `false`, of course*

2\. Then merge your config with the default one of Contao Encore Bundle. For this adjust your bundle's `Plugin.php` by implementing `Contao\ManagerPlugin\Config\ExtensionPluginInterface` and adding the code in `getExtensionConfig()`:

```php
class Plugin implements BundlePluginInterface, ExtensionPluginInterface
{
    // ...
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container) {
        return ContainerUtil::mergeConfigFile(
            'huh_encore',
            $extensionName,
            $extensionConfigs,
            $container->getParameter('kernel.project_dir').'/vendor/acme/contao-my-project-bundle/src/Resources/config/config_encore.yml'
        );
    }
}
```

3\. After installing your bundle clear `var/cache` and run the Contao command `vendor/bin/contao-console encore:prepare`. This generates a file called `encore.bundles.js` in your project root.
    This file contains entries for all contao encore compatible bundles that are added by calling `encoreBundles.addEntries();` in your `webpack.config.js`.<br><br>
    _IMPORTANT: You have to call this command everytime you want your webpack entries to be updated, e.g. if you added new entries to your yml configuration or removed some._

### Dynamically importing common dependencies asynchronously

Sometimes you have webpack entries (i.e. dependencies) that should be loaded on *every* page, like jQuery or Bootstrap's JavaScript.

And sometimes you have webpack entries (i.e. dependencies) that should only be loaded, if necessary on a particular page. One option would be to use `require()` in all of your modules. But this would result in duplicate code in every module depending on your dependency module. In this case it's best to load this dependency on demand asynchronously (and only this atomic dependency so that it can be cached).

For this you can do a dynamic webpack import of a *chunk* (webpack code splitting). See the following code for an example:

```javascript
document.addEventListener("DOMContentLoaded", e => import(/* webpackChunkName: "some-dependency" */ './some-dependency.js').then(module => {
    // module now contains any exported functions or attributes
}));
```

*NOTE: Of course, you could also use the `import()` operator on some button click to improve performance even more.*

In order to make the `import()` operator work you must include babel's `syntax-dynamic-import` plugin and support chunks in your `webpack.config.js`:

```javascript
Encore.configureBabel(function(babelConfig) {
    babelConfig.plugins.push('syntax-dynamic-import');
});

//...

var config = Encore.getWebpackConfig();

config.output.chunkFilename = '[name].bundle.js';

// support symlinks
config.resolve.symlinks = false;

module.exports = config;
```

... and activate dynamic imports in your root page in Contao.

Look at the webpack Code Splitting documentation section for more info on this topic: https://webpack.js.org/guides/code-splitting