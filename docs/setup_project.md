# Project setup

Setup your project for encore bundle.

1. Install Encore bundle via composer 

    ```
    composer require heimrichhannot/contao-encore-bundle
    ```

2. Update your database

3. If you haven't already done, add encore base dependencies:

   ```
   yarn add @symfony/webpack-encore @babel/core webpack webpack-cli @babel/core @babel/preset-env --dev
   ```

4. Update/Create your webpack config file (`webpack.config.js`) in your project root.
   You can also use the __example config__ provided below as starting point!

    1. Require the generated `encore.bundles.js` (this file is generated with the encore:prepare command, see [Run Encore](../README.md#run-encore))
    
        ```js
        let encoreBundles = require('./encore.bundles');
        ```
    
    1. Call `encoreBundles.addEntries()`

5. Optional: Add entries.    
   You can now add entries from your project, if you maintain your assets in your project code. The easiest way would be to just add them in your webpack.config.js. But you can also add them from configuration, see [Bundle Setup](setup_bundle.md) for more information. 

## Example Config

This is a working webpack config with SCSS/SASS file compilation.

First add following additional yarn dependencies to your project:

```
yarn add postcss-loader sass-loader@^13.0.0 sass --dev
```

Update/ add your `webpack.config.js` file accordingly:

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