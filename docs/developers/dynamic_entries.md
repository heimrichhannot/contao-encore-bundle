# Dynamic entries

This document describes different ways to add entries from your code. 

> For most usecases, you should use the [PageAssetTrait](../developers.md#add-encore-entries-to-custom-template) instead!

### FrontendAsset service

Encore bundle comes with a service, `FrontendAsset`, to register your entrypoints.


Example with dependency injection (with encore bundle as hard dependency):

```php
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;

class AcmeController
{
    /**
     * @var ContainerInterface
     */
    private $frontendAsset;

    public function __construct(FrontendAsset $frontendAsset)
    {
        $this->frontendAsset = $frontendAsset;
    }

    public function __invoke()
    {
        $this->frontendAsset->addActiveEntrypoint('contao-acme-bundle');
    }
}
```

Example with ServiceSubscriber for loose dependency (recommended):

```php
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;

class AcmeController implements ServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke()
    {

        if (class_exists(FrontendAsset::class) && $this->container->has(FrontendAsset::class)) {
            $this->container->get(FrontendAsset::class)->addActiveEntrypoint('contao-acme-bundle');
        }
    }

    public static function getSubscribedServices()
    {
        $services = [];
        if (class_exists(FrontendAsset::class)) {
            $services[] = '?'.FrontendAsset::class;
        }
    
        return $services;
    }
}
```

Example with optional dependency injection:

```yaml
# services.yml
App/FrontendModule/MyModule:
    calls:
      - [setEncoreFrontendAsset, ['@?HeimrichHannot\EncoreBundle\Asset\FrontendAsset']]
```

```php
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;

class MyModule
{
    protected $encoreFrontendAsset;

    public function setEncoreFrontendAsset(FrontendAsset $encoreFrontendAsset): void {
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
use Contao\System;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;

if (class_exists(FrontendAsset::class) && System::getContainer()->has(FrontendAsset::class)) {
    System::getContainer()->get(FrontendAsset::class)->addActiveEntrypoint('contao-slick-bundle');
}
```