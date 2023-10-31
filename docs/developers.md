# Developers

This document contains additional information for developers working with encore bundle.

## Add entries from your code (frontend module, content element,...)

Since version 1.3 it is possible to add encore entries from your code. So for example the slider assets are automatically included, if the slider module is added to the page. 

The most simple method is to use the `PageAssetsTrait` of [Contao Encore Contracts](https://github.com/heimrichhannot/contao-encore-contracts).
Use this trait in your class in combination with `ServiceSubscriberInterface` and make sure your class is registered as service with autoconfigure activated.
Now you have a new method `addPageEntrypoint()` available.
This method allows you to just pass the encore entry name and, optional, pass fallback assets. 
The trait takes care for you if encore bundle is installed and register the fallback assets, if not.

```php
use HeimrichHannot\EncoreContracts\PageAssetsTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class FrontendController implements ServiceSubscriberInterface
{
    use PageAssetsTrait;
    
    public function __invoke()
    {
        $this->addPageEntrypoint(
            // Encore entry point name
            'contao-example-bundle', 
             // Optional: define fallback assets to use if encore bundle is not installed
            [
                'TL_CSS' => ['main-theme' => 'assets/main/dist/main-theme.min.css|static'],
                'TL_JAVASCRIPT' => [
                    'main-theme' => 'assets/main/dist/main-theme.min.js|static',
                    'some-dependency' => 'assets/some-dependency/some-dependency.min.js|static',
                ],
            ]
        );
    }
}
```

There are other ways to add entries from your code, see [dynamic entries](developers/dynamic_entries.md).

## Events

| Event              | Description                                  |
|--------------------|----------------------------------------------|
| EncoreEnabledEvent | Add custom logic to enable encore on a page. |


## Add encore entry select to your dca 

To add an encore entry select to your dca like in layout or page settings, you can use the `EncoreEntriesSelectField` class.

```php
# config/dca/tl_example.php
EncoreEntriesSelectField::register('tl_example')
    ->setIncludeActiveCheckbox(true);

PaletteManipulator::create()
    ->addField('encoreEntries', 'layout_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_example');
```


## Add encore entries to custom template

If you don't want to render assets on page basis, it is possible to generate a custom set of encore entries.

1. Create an `EntrypointCollection` with the `EntrypointCollectionFactory` service
1. Get your assets with `TemplateAssetGenerator` service. 
1. Optional: If you want an input field in the contao backend to select entries, you can use the `DcaGenerator` service to generate an input like on layout or page settings.

```php
use Contao\FrontendTemplate;
use HeimrichHannot\EncoreBundle\Asset\EntrypointCollectionFactory;
use HeimrichHannot\EncoreBundle\Asset\TemplateAssetGenerator;

function renderTemplateWithEncore(array $entrypoints, EntrypointCollectionFactory $entrypointCollectionFactory, TemplateAssetGenerator $templateAssetGenerator)
{
    $template = new FrontendTemplate();
    $collection = $entrypointCollectionFactory->createCollection($entrypoints);
    $template->stylesheets = $templateAssetGenerator->linkTags($collection);
    $template->headJavaScript = $templateAssetGenerator->headScriptTags($collection);
    $template->javaScript = $templateAssetGenerator->scriptTags($collection);
    return $template->getResponse();
}
```

It is also possible to get the stylesheets inline:

```php
use Contao\FrontendTemplate;
use HeimrichHannot\EncoreBundle\Asset\EntrypointCollectionFactory;
use HeimrichHannot\EncoreBundle\Asset\TemplateAssetGenerator;

function renderTemplateWithEncore(array $entrypoints, EntrypointCollectionFactory $entrypointCollectionFactory, TemplateAssetGenerator $templateAssetGenerator)
{
    $template = new FrontendTemplate();
    $collection = $entrypointCollectionFactory->createCollection($entrypoints);
    $template->inlineCss = $templateAssetGenerator->inlineCssLinkTag($collection);
    return $template->getResponse();
}
```

## ConfigurationHelper

The `ConfigurationHelper` service can be used to obtain some configuration information. Following methods are available:

`isEnabledOnCurrentPage(?PageModel $pageModel = null): bool` - Return if encore is enabled for the current frontend page. You can pass a page object to check for a custom page, otherweise `global $objPage` is used.

`getRelativeOutputPath(): string` - Return the relative output path configured by webpack encore bundle. Typical this is `build`.

`getAbsoluteOutputPath(): string` - Return the absolute output path configured by webpack encore bundle. For example `/var/www/html/project/web/build`

## Custom import templates

If you need custom templates for the import of javascript and stylesheet assets files, Encore Bundle provide support for this. 
Create a twig template (see `src/Resources/views` for examples) and register them in your (project) bundle config.

Example:

```yaml
huh_encore:
  templates:
      imports:
      - { name: default_css, template: "@HeimrichHannotEncore/encore_css_imports.html.twig" }
      - { name: default_js, template: "@HeimrichHannotEncore/encore_js_imports.html.twig" }
```