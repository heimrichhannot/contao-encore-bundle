# Bundle setup

With encore bundle you can prepare your bundles to automatically create encore entries. These entries will be added to the entrypoints.json without any modification of your webpack configuration.

Since version 1.16 you do not need to add Encore bundle as hard dependency to your bundle as we extracted all necessary stuff into 
[Contao Encore Contracts](https://github.com/heimrichhannot/contao-encore-contracts), which contains all necessary interfaces and classes to make your bundle
compatible to encore bundle.


1. Require `heimrichhannot/contao-encore-contracts` in your bundle composer.json
   
   ```
   composer require heimrichhannot/contao-encore-contracts
   ```
2. Create an EncoreExtension class implementing `\HeimrichHannot\EncoreContracts\EncoreExtensionInterface`: 

   ```php
   namespace HeimrichHannot\ExampleBundle\Asset;
   
   use HeimrichHannot\EncoreContracts\EncoreEntry;
   use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
   use HeimrichHannot\ExampleBundle\HeimrichHannotExampleBundle;
   
   class EncoreExtension implements EncoreExtensionInterface
   {
       public function getBundle(): string
       {
           // Return the bundle class
           return HeimrichHannotExampleBundle::class;
       }
   
       public function getEntries(): array
       {
           // Return the bundle entries
           return [
               EncoreEntry::create('main-theme', 'assets/main/js/main-theme.js')
                   ->setRequiresCss(true)
                   ->setIsHeadScript(false),
               EncoreEntry::create('one-pager', 'assets/one-pager/js/one-pager.js')
                   ->setRequiresCss(true),
               EncoreEntry::create('custom-head-js', 'assets/main/js/head.js')
                   ->setIsHeadScript(true)
                   // Define entries that will be removed from the global asset array
                   ->addJsEntryToRemoveFromGlobals('colorbox')
                   ->addCssEntryToRemoveFromGlobals('css-to-replace'),
           ];
       }
   }
   ```

   Explanation:    
   - Create for every bundle encore entrypoint an `EncoreEntry` instance with entry name and relative path from bundle 
   root to the javascript file.    
   - If your entry requires css, call `setRequiresCss(true)`.    
   - If your entry javascript should be added in the head section of your page, call `setIsHeadScript(true)`.
     Otherwise, it will be added the buttom of the page.    
   - If your entry should replace entries within the contao global asset array, you can use the `add*EntryToRemoveFromGlobals()` methods.
     The given names must match the keys in the contao global asset array. 
     For example `addJsEntryToRemoveFromGlobals('colorbox')` will unset `$GLOBALS['TL_JAVASCRIPT']['colorbox']`
3. Register your EncoreExtension class as service with autoconfigure set to true

   ```yaml
   # config/services.yml
   services:
      HeimrichHannot\ExampleBundle\Asset\EncoreExtension:
         autoconfigure: true
   ```

## Next steps:

- If your javascript code had dependencies on node packages, you can require them in you bundle `package.json`.
   The prepare command will add the dependencies to your project dependencies. 
- If your bundle need an encore entry to be loaded to work (e.g. if it's needed for a frontend module or widget), you can load entries from you code. See [developers documentation](developers.md) for how to do that.

