# Upgrade

Upgrade notice for breaking changes

## Theme-level Encore configuration

Run `contao:migrate` after updating the bundle to apply the data migration and database schema changes.

Encore can now be activated at theme level. Layouts use "Inherit", "Enable" or "Disable" instead of a checkbox. New themes start disabled and new layouts inherit their theme's setting.

The migration preserves existing behavior: previously enabled layouts remain explicitly enabled, and previously disabled layouts become explicitly disabled. Existing entry selections are unchanged. To use theme activation for an existing layout, switch that layout to "Inherit" after the migration.

For integrations reading `tl_layout.addEncore` directly, the values are now `i` (inherit), `1` (enable), and `0` (disable). Do not evaluate the field as a boolean: use `ConfigurationHelper::isEnabledForLayout()` to resolve the theme setting. Legacy empty values remain disabled until migrated.

Theme entries are inherited by layouts and pages. Layout and page entries retain their per-entry "Active" checkbox and can override inherited entries.


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

