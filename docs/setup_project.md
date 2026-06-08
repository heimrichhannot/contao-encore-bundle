# Project setup

This document describes how to setup your project for the encore bundle.

## Setup steps

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

[5. Optional: Add entries.
   There are two ways to add encore entries: directly from your `webpack.config.js` or from encore extension.
   
   From `webpack.config.js`
   ```js
   // webpack.config.js
   Encore
   .addEntry('app_theme', './resources/themes/app/js/theme.js')
   // ...
   ```   

   You can also create a encore extension in your App. 
   Return `App` in the `EncoreExtensionInterface::getBundleName()` method implementation 
   or extend the `AbstractEncoreExtension`, which already do that for you.

   ```php
    // src/Asset/EncoreExtension.php
   namespace App\Asset;
   
   use HeimrichHannot\EncoreBundle\EncoreExtension\AbstractProjectEncoreExtension;
   use HeimrichHannot\EncoreContracts\EncoreEntry;
   
   class EncoreExtension extends AbstractProjectEncoreExtension
   {
       public function getEntries(): array
       {
           return [
               EncoreEntry::create('app_theme', 'resources/themes/app/js/theme.js')
                    ->setRequiresCss(true),
                EncoreEntry::create('app_stuff', 'resources/themes/app/js/stuff.js')
                   ->setIsHeadScript(true)
                   ->setDefer(true),
           ];
       }
   }
   ```


## Example Config

This is a working webpack config with SCSS/SASS file compilation.

First add following additional yarn dependencies to your project:

```
yarn add postcss-loader sass-loader sass --dev
```

Update/ add your `webpack.config.js` file accordingly:

```javascript
// webpack.config.js

 let Encore = require('@symfony/webpack-encore'),
     encoreBundles = require('./encore.bundles');
 
 Encore
     // project entries
     // .addEntry('app', './resources/js/app.js')
         
     //build config
     .setOutputPath('public/build/')
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

## Further information
- We recommend adding corejs polyfill (former babel polyfill) into your setup, see [Javascript setup section](setup_javascript.md) for more informations.