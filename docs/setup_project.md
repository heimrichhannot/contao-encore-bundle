# Project setup

Setup your project for encore bundle.

1. Install Encore bundle via composer 

    ```
    composer require heimrichhannot/contao-encore-bundle
    ```

1. Update your database

1. Create your webpack/encore config file (`webpack.config.js`) in your project root.

    1. Require the generated `encore.bundles.js` (this file is generated with the encore:prepare command, see [Run Encore](../README.md#run-encore))
    
        ```js
        let encoreBundles = require('./encore.bundles');
        ```
    
    1. Call `encoreBundles.addEntries()`

        Example:  
        
        ```javascript
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
        
        config.output.chunkFilename = '[name].bundle.js';
        
        // support symlinks
        config.resolve.symlinks = false;
        
        module.exports = config;
        ```
        
        We recommend adding corejs polyfill (former babel polyfill) into your setup, see [Javascript setup section](setup_javascript.md) for more informations.

1. Update your `fe_page` template or use the bundled `fe_page_encore_bundle` template. Following changes to your template are necessary: 
    1. Add the following in `<head>` region:

        ```php
        <?= $this->encoreStylesheets; ?>
        <?= $this->encoreHeadScripts; ?>
        ```
    
    1. Add the following into the footer region:
    
        ```php
        <?= $this->encoreScripts; ?>
        `````