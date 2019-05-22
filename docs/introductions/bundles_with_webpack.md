# Install frontend assets using Webpack/Encore

If you want to add the frontend assets (JS & CSS) to your project using webpack, please add [foxy/foxy](https://github.com/fxpio/foxy) to the dependencies of your project's `composer.json` and add the following to its `config` section:

```json
"foxy": {
  "manager": "yarn",
  "manager-version": "^1.5.0"
}
```

Using this, foxy will automatically add the needed yarn packages to your project's `node_modules` folder.

If you don't want to have duplicate asset files (we know you don't want), you also need to unset the corresponding entries in `$GLOBALS['TL_JAVASCRIPT']`. If you use our [Encore bundle](https://github.com/heimrichhannot/contao-encore-bundle), we do the work for you, at least for prepared bundles. 

If you want to specify which frontend assets to use on a per page level, you can use [heimrichhannot/contao-encore-bundle](https://github.com/heimrichhannot/contao-encore-bundle). 

## Packages

### Modernizr
If you need to configure [Modernizr] for your project, you need to update your webpack config and add an modernizr configuration file.

The following provided example setup uses [Symfony Webpack Encore](https://github.com/symfony/webpack-encore) and [webpack-modernizr-loader](https://github.com/itgalaxy/webpack-modernizr-loader).

```javascript
// webpack.config.js

Encore
// Add modernizr as shared entry: 
.addEntry('vendor', 'modernizr')
// Add modernizr loader
.addLoader({
    loader: "webpack-modernizr-loader",
    test: /\.modernizrrc\.js$/
})
// Add path to modernizr config as alias
.addAliases({
    modernizr$: path.resolve(__dirname, '.modernizrrc.js')
})
;
```

```javascript
// .modernizrrc.js
module.exports = {
    // Your modernizr config, like:
    "options": [
        "setClasses",
    ],
    "feature-detects": [
        "test/touchevents"
    ]
}
```

Update the config file(`.modernizrrc.js`) with the necessary configuration. 

If you need to add Modernizr by yourself to a bundle, just add following line to your main bundle javascript file:

```javascript
window.Modernizr = global.Modernizr = require('modernizr');
```
