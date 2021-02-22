# Project setup

Setup your project for encore bundle.

1. Install Encore bundle via composer 

    ```
    composer require heimrichhannot/contao-encore-bundle
    ```

1. Update your database

1. Update/Create your webpack config file (`webpack.config.js`) in your project root.

    1. Require the generated `encore.bundles.js` (this file is generated with the encore:prepare command, see [Run Encore](../README.md#run-encore))
    
        ```js
        let encoreBundles = require('./encore.bundles');
        ```
    
    1. Call `encoreBundles.addEntries()`
    
    Example:  
    
    ```javascript
   // webpack.config.js
   
    let Encore = require('@symfony/webpack-encore'),
        encoreBundles = require('./encore.bundles');
    
    Encore
        .setOutputPath('web/build/')
        .setPublicPath('/build')
        .cleanupOutputBeforeBuild()
        .enableVersioning()
    
        // css
        .enableSassLoader()
        .enablePostCssLoader()
    
        // js
        .enableSourceMaps(!Encore.isProduction())
        
        .splitEntryChunks()
        .enableSingleRuntimeChunk()
    ;
    
    // this function adds entries for all contao encore compatible bundles automatically
    // -> the source of that is the file "encore.bundles.js" in your project root which is
    // generated automatically using the contao command "vendor/bin/contao-console encore:prepare"
    // -> you can pass an array to the function if you want to skip certain entries
    encoreBundles.addEntries();
    
    // support dynamic chunks
    let config = Encore.getWebpackConfig();
    
    // support symlinks
    config.resolve.symlinks = false;
    
    module.exports = config;
    ```
        
   > We recommend adding corejs polyfill (former babel polyfill) into your setup, see [Javascript setup section](setup_javascript.md) for more informations.

1. Set `huh_encore.use_contao_template_variables` to true

```yaml
# config/config.yml (Contao >= 4.9)
# app/config/config.yml (Contao 4.4)
huh_encore:
  use_contao_template_variables: true

```

> This option was added to use the default contao fe_page template variables instead of custom variables from this bundle. The old implementation is considered deprecated and will be removed in a future version. If you still want or need to use it, see `src/Resources/contao/templates/fe_page_encore_bundle.html5` for usage.

1. Optional: Add entries.    
   You can now add entries from your project, if you maintain your assets in your project code. The easiest way would be to just add them in your webpack.config.js. But you can also add them from configuration, see [Bundle Setup](setup_bundle.md) for more information. 