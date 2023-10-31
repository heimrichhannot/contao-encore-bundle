<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle;

use HeimrichHannot\EncoreBundle\DependencyInjection\EncoreConfigCompilerPass;
use HeimrichHannot\EncoreBundle\DependencyInjection\EncoreExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotEncoreBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new EncoreExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new EncoreConfigCompilerPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }


}
