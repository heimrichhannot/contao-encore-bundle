# Upgrade

Upgrade notice for breaking changes


## 0.* to 1.0

The main encore configuration moved from root page to layout settings. We provide an runonce, which should cover the most cases for moving the configuration, but you should check the configuration. Also no entries are transferred, this must be done manually (since the entries in the page root still exist, everthing should also work if no entries transferred).

## 0.2 to 0.4

The are changes to increasing encore version to 0.22 and use symfony encore bundle.

### Shared entries

OLD

```js
// webpack.config.js
Encore
// ...
.createSharedEntry('vendor', [
        'jquery',
        'bootstrap'
    ])
```

NEW

Require entries in your project js instead.

```js
// contao-project-bundle.js
require('jquery');
require('bootstrap');
```

### Babel
Babel version was increased to 7

OLD: 

```js
// webpack.config.js
Encore
// ...
.addEntry('babel-polyfill', [
    'babel-polyfill'
])
```

NEW: 
```js
// webpack.config.js
Encore
// ...
.addEntry('babel-polyfill', [
    '@babel/polyfill'
])

```

### Split entries

If you want your dependencies to be just included one (instead of added to every file require them), add:

```js
// webpack.config.js
Encore
// ...
.splitEntryChunks()
.enableSingleRuntimeChunk()
```

