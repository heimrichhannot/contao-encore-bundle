# Developers

This document contains additional information for developers working with encore bundle.

## Add entries from your code (frontend module, content element,...)

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

To collect or render assets in custom templates or abstinent from the normal page rendering, use the `EntryPointsBuilder`.

```php
<?php

namespace App\CustomController;

use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointBuilderFactory;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;use Twig\Environment;

class CustomController
{
    private readonly TagRenderer $tagRenderer;
    private readonly EntryPointBuilderFactory $entrypointBuilderFactory;
    private readonly Environment $twig;
    private readonly FrontendAsset $frontendAsset;

    public function __invoke(): Response
    {
        // collect entry points from the different sources
        $entryPoints = $this->entrypointBuilderFactory->create()
            // add the sources you want: 
            ->setPage($event->getPage())
            ->setLayout($event->getLayout())
            ->setFrontendAsset($this->frontendAsset)
            // build the collection:
            ->build();

        // render the tags, for example with the tag renderer of webpack encore bundle
        $this->tagRenderer->reset();
        $css = $head = $body = [];
        foreach ($entryPoints->allActive() as $entrypoint) {
            if ($entrypoint->requiresCss) {
                $css[] = $this->tagRenderer->renderWebpackLinkTags($entrypoint->name);
            }
            if ($entrypoint->head) {
                $head[] = $this->tagRenderer->renderWebpackScriptTags($entrypoint->name);
            } else {
                $body[] = $this->tagRenderer->renderWebpackScriptTags($entrypoint->name);
            }
        }
        
        // render the template
        return new Response($this->twig->render('custom_template.html.twig', [
            'css' => $css,
            'head' => $head,
            'body' => $body,
        ]));
    }
}
```

## ConfigurationHelper

The `ConfigurationHelper` service can be used to obtain some configuration information. Following methods are available:

`isEnabledOnPage(PageModel $page, ?LayoutModel $layout = null): bool` - Return if encore is enabled for the current frontend page.

`getRelativeOutputPath(): string` - Return the relative output path configured by webpack encore bundle. Typical this is `build`.

`getAbsoluteOutputPath(): string` - Return the absolute output path configured by webpack encore bundle. For example `/var/www/html/project/public/build`