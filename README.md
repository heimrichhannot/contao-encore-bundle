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
5. In your `fe_page.html5` add the following before `<?= $this->head ?>`:<br>
   `<?= $this->encore ?>`
   This will add the necessary link and script tags automatically.
6. If one of your webpack entries requires jQuery, of course you can deactivate jQuery in the Contao layout now.

## Configuration

### Define webpack entries for Contao Encore Bundle

In webpack an entry usually results in a JavaScript file generated in an output directory. These files are included in the final HTML page.

Please do the following steps:

1\. At first webpack entries must be specified in your `webpack.config.js` (as normally with webpack). Here's how this file could look like:

```javascript
var Encore = require('@symfony/webpack-encore');

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
    .autoProvidejQuery()
    .enableSourceMaps(!Encore.isProduction())
    .createSharedEntry('vendor', [
        'jquery',
        'bootstrap'
    ])

    // babel polyfill e.g. for IE <= 11 Promise support (with Contao Encore Bundle this entry is added only if necessary, i.e. for IE <= 11)
    .addEntry('babel-polyfill', [
        'babel-polyfill'
    ])

    // bundles
    .addEntry('contao-list-bundle', './vendor/heimrichhannot/contao-list-bundle/src/Resources/public/js/jquery.list-bundle.es6.js')

    // project
    .addEntry('contao-my-project-bundle', './vendor/acme/contao-my-project-bundle/src/Resources/assets/js/jquery.my-project-bundle.js')
;

// support dynamic chunks
var config = Encore.getWebpackConfig();

config.output.chunkFilename = '[name].bundle.js';

// support symlinks
config.resolve.symlinks = false;

module.exports = config;
```

2\. Now in order to make your entries for Contao Encore Bundle you have to create a `config_encore.yml` file (or whatever name you like) in your bundle containing the following structure:

```yaml
huh:
    encore:
        entries:
            - { name: contao-my-project-bundle, file: "vendor/acme/contao-my-project-bundle/src/Resources/public/js/jquery.my-project-bundle.js", requiresCss: true }
```

*NOTE: If your entry doesn't require any css, set `requiresCss` to `false`, of course*

3\. The last step is to merge your config with the default one of Contao Encore Bundle. For this adjust your bundle's `Plugin.php` by implementing `Contao\ManagerPlugin\Config\ExtensionPluginInterface` and adding the code in `getExtensionConfig()`:

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