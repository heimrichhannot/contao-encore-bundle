<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use HeimrichHannot\EncoreBundle\HeimrichHannotContaoEncoreBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\WebpackEncoreBundle\WebpackEncoreBundle;

class Plugin implements BundlePluginInterface, ConfigPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(WebpackEncoreBundle::class),
            BundleConfig::create(HeimrichHannotContaoEncoreBundle::class)->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }

	/**
	 * Allows a plugin to load container configuration.
	 */
	public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
	{
        $loader->load('@HeimrichHannotContaoEncoreBundle/Resources/config/config.yml');
        $loader->load('@HeimrichHannotContaoEncoreBundle/Resources/config/commands.yml');
        $loader->load('@HeimrichHannotContaoEncoreBundle/Resources/config/listeners.yml');
        $loader->load('@HeimrichHannotContaoEncoreBundle/Resources/config/services.yml');
	}
}

