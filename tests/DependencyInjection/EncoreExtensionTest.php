<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\DependencyInjection;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\DependencyInjection\EncoreExtension;
use HeimrichHannot\EncoreBundle\Exception\FeatureNotSupportedException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EncoreExtensionTest extends ContaoTestCase
{
    public function testGetAlias()
    {
        $extension = new EncoreExtension();
        $this->assertSame('huh_encore', $extension->getAlias());
    }

    public function testPrepend()
    {
        //
        // Preconfigured public path
        //

        $extension = new EncoreExtension();
        $container = $this->createMock(ContainerBuilder::class);
        $container->method('getExtensionConfig')->willReturn([
            ['output_path' => 'web/build'],
        ]);
        $container->expects($this->never())->method('getParameter');
        $extension->prepend($container);

        //
        // test without composer public_path setting
        //

        $container = $this->createMock(ContainerBuilder::class);
        $container->method('getExtensionConfig')->willReturn([]);
        $container->method('prependExtensionConfig')->with(
            $this->stringContains('webpack_encore'),
            $this->callback(function ($subject) {
                return str_contains(($subject['output_path'] ?? ''), '%kernel.project_dir%');
            }));
        $extension->prepend($container);

        //
        // test with public_path = web
        //

        $container = $this->createMock(ContainerBuilder::class);
        $container->method('getExtensionConfig')->willReturn([]);
        $container->method('getParameter')->willReturn(__DIR__.'/../Fixtures/DependencyInjection/public_dir_web');
        $container->method('prependExtensionConfig')->with(
            $this->stringContains('webpack_encore'),
            $this->callback(function ($subject) {
                return str_contains(($subject['output_path'] ?? ''), '/web/build');
            }));
        $extension->prepend($container);

        //
        // test with public_path = public
        //

        $container = $this->createMock(ContainerBuilder::class);
        $container->method('getExtensionConfig')->willReturn([]);
        $container->method('getParameter')->willReturn(__DIR__.'/../Fixtures/DependencyInjection/public_dir_public');
        $container->method('prependExtensionConfig')->with(
            $this->stringContains('webpack_encore'),
            $this->callback(function ($subject) {
                return str_contains(($subject['output_path'] ?? ''), '/public/build');
            }));
        $extension->prepend($container);

        //
        // With multiple builds
        //

        $container = $this->createMock(ContainerBuilder::class);
        $container->method('getExtensionConfig')->willReturn([
            ['output_path' => false],
        ]);

        $exception = false;
        try {
            $extension->prepend($container);
        } catch (FeatureNotSupportedException $e) {
            $exception = true;
        }

        $this->assertTrue($exception);
    }
}
