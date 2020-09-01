# Developers

This document contains additional information for developers working with encore bundle.

## Add entries from your code (frontend module, content element,...)

Since version 1.3 it is possible to add encore entries from your code. So for example the slider assets are automatically included, if the slider module is added to the page. To do this, you can use the `FrontendAsset` service.

Example with optional dependency injection (recommended): 

```yaml
# services.yml
App/FrontendModule/MyModule:
    calls:
      - [setEncoreFrontendAsset, ['@?HeimrichHannot\EncoreBundle\Asset\FrontendAsset']]
```

```php
class MyModule
{
    protected $encoreFrontendAsset;

    public function setEncoreFrontendAsset(\HeimrichHannot\EncoreBundle\Asset\FrontendAsset $encoreFrontendAsset): void {
        $this->encoreFrontendAsset = $encoreFrontendAsset;
    }

    public function getResponse() {
        // ...
        if ($this->encoreFrontendAsset) {
            $this->encoreFrontendAsset->addActiveEntrypoint('mymodule-assets');
        }
        //...
    }
}
```

Example for legacy code (old frontend modules or content elements): 

```php
if (\Contao\System::getContainer()->has('huh.encore.asset.frontend')) {
    \Contao\System::getContainer()->get('huh.encore.asset.frontend')->addActiveEntrypoint('contao-slick-bundle');
}
```

## Make encore bundle an optional dependency

If you create a reusable bundle and want to support setups that don't use encore, you need adjust the encore bundle configuration:

1. Move your `huh_encore` configuration to an own config file, for example `config_encore.yml`.

1. In your `Plugin` class implement the `ConfigPluginInterface` and load the config, if encore bundle is installed. 

    ```php
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        if (class_exists('HeimrichHannot\EncoreBundle\HeimrichHannotContaoEncoreBundle')) {
            $loader->load("@HeimrichHannotVideoBundle/Resources/config/config_encore.yml");
        }
    }
    ```

1. Optional: Add encore bundle to your composer.json suggest section.

    ```json
    "suggest": {
        "heimrichhannot/contao-encore-bundle": "Symfony Webpack Encore integration for Contao.",
      }
    ``` 

## Add encore entries to custom template

If your template generation don't rely on the onGeneratePage hook, it is possible to encore entries to your own implementation. Use the `TemplateAsset` service to create an instance of it and add the assets you need to your template. Following example is an short version of how the onGeneratePage hook is implemented.

```php
class CustomTemplateGenerator 
{
    /**
     * @var \HeimrichHannot\EncoreBundle\Asset\TemplateAsset
     */
    private $templateAsset;

    public function addEncoreToTemplate(FrontendTemplate $template, \Contao\PageModel $page, \Contao\LayoutModel $layout)
    {
        $templateAssets = $this->templateAsset->createInstance($page, $layout);
        $template->encoreStylesheets = $templateAssets->linkTags();
        $template->encoreScripts = $templateAssets->scriptTags();
        $template->encoreHeadScripts = $templateAssets->headScriptTags();
    }
}
```

It is also possible to make this optional using TemplateAsset public service alias (`huh.encore.asset.template`):

```php
// Example from heimrichhannot/contao-amp-bundle

class GeneratePageListener
{
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function onGeneratePage(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        // [...]
        
        if ($this->container->has('huh.encore.asset.template')) {
            $templateAssets = $this->container->get('huh.encore.asset.template')->createInstance($pageModel, $layout);
            $pageRegular->Template->encoreStylesheetsInline = preg_replace('/@charset ".*?";/m', '', $templateAssets->inlineCssLinkTag());
        }
    }
}
```

## Inline stylesheets

If you need to add your stylesheets inline, use the `inlineCssLinkTag` method of `TemplateAsset` (see 'Add encore entries to custom template'). If your template rely on the onGeneratePage hook, you need to unset the hook entries of encore bundle.

```php
class HookListener 
{
    /**
     * @var \HeimrichHannot\EncoreBundle\Asset\TemplateAsset
     */
    private $templateAsset;

    public function onGetPageLayout(PageModel $page, LayoutModel &$layout, PageRegular $pageRegular)
    {
        if (isset($GLOBALS['TL_HOOKS']['generatePage']['huh.encore-bundle'])) {
            unset($GLOBALS['TL_HOOKS']['generatePage']['huh.encore-bundle']);
        }
    }
    public function onGeneratePage(PageModel $page, LayoutModel $layout, PageRegular $pageRegular) 
    {
        /** @var string $encoreField The dca field name where encore entries stored */
        $templateAssets = $this->templateAsset->createInstance($page, $layout, $encoreField);
        $pageRegular->Template->encoreEntriesAmp = $templateAssets->inlineCssLinkTag();
    }
}
```

## Custom import templates

If you need custom templates for the import of javascript and stylesheet assets files, Encore Bundle provide support for this. 
Create a twig template (see `src/Resources/views` for examples) and register them in your (project) bundle config.

Example:

```yaml
huh_encore:
  templates:
      imports:
      - { name: default_css, template: "@HeimrichHannotContaoEncore/encore_css_imports.html.twig" }
      - { name: default_js, template: "@HeimrichHannotContaoEncore/encore_js_imports.html.twig" }
```