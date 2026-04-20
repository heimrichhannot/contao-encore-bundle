<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

declare(strict_types=1);

use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointBuilderFactory;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->defaults()
            ->autowire()
            ->bind('$bundleConfig', '%huh_encore%')
            ->bind('$webDir', '%contao.web_dir%')
            ->bind('$encoreCache', service('webpack_encore.cache'))
            ->bind(CacheItemPoolInterface::class, service('webpack_encore.cache'))
            ->bind(TagRenderer::class, service('webpack_encore.tag_renderer'))
    ;

    $services
        ->load('HeimrichHannot\\EncoreBundle\\', '../src/{Asset,Collection,Command,DataContainer,EventListener,Helper}/*')
            ->exclude('../src/Asset/{EntrypointCollection.php}')
            ->public()
            ->autoconfigure()
    ;

    $services
        ->set(EntryPointBuilderFactory::class)
    ;

    $services
        ->alias('huh.encore.asset.frontend', FrontendAsset::class)
            ->public()
    ;

    $services
        ->alias('huh.encore.asset.template', TemplateAsset::class)
            ->public()
    ;
};
