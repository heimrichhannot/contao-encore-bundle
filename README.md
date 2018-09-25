# Contao Encore Bundle

This bundle offers the opportunity to specify which webpack entries (JavaScript, CSS, i.e. whatever webpack supports) should be loaded on a per page level. 3rd party bundles are also supported if they're made compatible.

## Features

- conditionally load your project assets only if necessary on a particular page
- asynchronously load shared dependency entries *on demand and cached* using webpack import() operator (see chapter "Dynamically importing common dependencies asynchronously")

## Setup 

### Prerequisites

* Read the [Encore Documentation](https://symfony.com/doc/current/frontend.html) in order to install Encore and understand the core concepts of Webpack and Symfony Encore.

Optional:

* Add [Foxy](docs/introductions/bundles_with_webpack.md) to your project (for bundle dependency management)

### Project setup

**1\.** Install Encore bundle via composer 

```composer require heimrichhannot/contao-encore-bundle```

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
    * `name`: Will be shown in contao backend and will be used as alias/identifier in the datebase. Required.
    * `file`: Path to the Javascript file. Required
    * `requireCss`: Set to true, if entry requires css.
    * `head`: Set to true, if entry should added to the `encoreHeadScripts` section (see project setup) in your page layout instead to the bottom (CSS will always be added to the head).
* Within `legacy` you can define assets, that will be stripped from the global contao arrays. Here you can add assets, that you seve with webpack, so they won't be loaded twice or on the wrong page. IMPORTANT: The strings defined here must match the array keys in Contao's global arrays
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


### Run Encore

**1\.** Clear your cache (`vendor/bin/contao-console cache:clear -e dev`)

**2\.** Run the Contao command `vendor/bin/contao-console encore:prepare`. This generates a file called `encore.bundles.js` in your project root.
This file contains entries for all contao encore compatible bundles that are added by calling `encoreBundles.addEntries();` in your `webpack.config.js`.

_IMPORTANT: You have to call this command everytime you want your webpack entries to be updated, e.g. if you added new entries to your yml configuration or removed some._

**3\.** Now run `yarn encore dev --watch` to generate the final CSS.

**4\.** If the generation succeeded without errors, you can now active encore entries. See Usage -> Activate encore entries for  how to do that.



## Usage

### Activate encore entries

* Go in the contao backend to site structure and choose the website root
* Scroll to encore settings and check "Activate Webpack Encore"
* fill the (mandatory) fields
* if you have an main project bundle entry containing the main stylesheets, add it as active entry, add also all other entries you want to have activated on every page. Be patient that you check all entries as active (if you want them to be loaded)!
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
> If you use jQuery in webpack, you can deactive in the contao page layout to don't have include it twice.

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