# Bundle setup

With encore bundle you can prepare your bundles to automatically create encore entries. These entries will be added to the entrypoints.json, without any modification of your webpack configuration.

1. Add your bundle encore entries to your bundle config, typical `src/Resources/config/config.yml`. Full-featured example:

    ```yaml
    huh_encore:
      js_entries:
        - name: contao-my-project-bundle
          requires_css: true
          head: false
          file: "vendor/acme/contao-my-project-bundle/src/Resources/public/js/my-project-bundle.js"
        - name: special-feature
          requires_css: true
          head: false
          file: "vendor/acme/contao-my-project-bundle/src/Resources/public/js/awesome-but-rare-used-feature.js"
      unset_global_keys:
        js:
        - contao-my-project-bundle
        - some-other-dependency
        jquery:
        - my-jquery-dependency
        css:
        - contao-my-project-bundle
    ```

    Explanation:
    * Within `js_entries` you register Javascript files, which can be activated from the Contao backend
        * you can register multiple entries per bundle, so you don't need to include all files/features in every page
        * `name`: Will be shown in contao backend and will be used as alias/identifier in the database. Required.
        * `file`: Path to the Javascript file. Required
        * `require_css`: Set to true, if entry requires css.
        * `head`: Set to true, if entry should added to the `encoreHeadScripts` section (see project setup) in your page layout instead to the bottom (CSS will always be added to the head).
    * Within `unset_global_keys` you can define assets, that will be stripped from the global contao arrays. Here you can add assets, that you serve with webpack, so they won't be loaded twice or on the wrong page. IMPORTANT: The strings defined here must match the array keys in Contao's global arrays
        * `js`: Assets will be stripped from `$GLOBALS['TL_JAVASCRIPT']`
        * `jquery`: Assets will be stripped from `$GLOBALS['TL_JQUERY']`
        * `css`: Assets will be stripped from `$GLOBALS['TL_USER_CSS']` and `$GLOBALS['TL_CSS']`
     * Visit [Configuration](configuration.md) for a complete overview about possible configuration options

1. If your config isn't already registered in your Contao Manager `Plugin` class (or in the bundle extension class), you need to do this now: implement the `ConfigPluginInterface` class and register the config in the new `registerContainerConfiguration` method:

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

1. Add encore bundle to your composer.json file (See Project setup step 1).

    > If you want encore bundle to be an optional dependency, please consider the [developers documentation](developers.md).

1. Optional: You probably want to have your bundle's node dependencies added automatically to the project's node_modules directory when installed. You can simply use [Foxy](https://github.com/fxpio/foxy) for this task. To keep it simple: besides having foxy installed in your project, you need to set `"foxy": true` in the `extra` section of your bundle's `composer.json` and add an ordinary `package.json` as usual for node modules. See [heimrichhannot/contao-list-bundle](https://github.com/heimrichhannot/contao-list-bundle) for an example.

1. Optional: If your bundle need an encore entry to be loaded to work (e.g. if it's needed for a frontend module or widget), you can load entries from you code. See [developers documentation](developers.md) for how to do that.

