<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Helper;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ConfigurationHelperTest extends ContaoTestCase
{
    public function createTestInstance(array $parameters = [])
    {
        $containerUtil = $parameters['containerUtil'] ?? $this->createMock(ContainerUtil::class);
        $requestStack = $parameters['requestStack'] ?? $this->createMock(RequestStack::class);
        $modelUtil = $parameters['modelUtil'] ?? $this->createMock(ModelUtil::class);
        $bundleConfig = $parameters['bundleConfig'] ?? [];
        $webDir = $parameters['webDir'] ?? '';

        $instance = new ConfigurationHelper(
            $containerUtil,
            $requestStack,
            $modelUtil,
            $bundleConfig,
            $webDir
        );

        return $instance;
    }

    public function testIsEnabledOnCurrentPage()
    {
        $containerUtil = $this->createMock(ContainerUtil::class);
        $containerUtil->method('isFrontend')->willReturn(false);
        $instance = $this->createTestInstance([
            'containerUtil' => $containerUtil,
        ]);
        $this->assertFalse($instance->isEnabledOnCurrentPage());

        $containerUtil = $this->createMock(ContainerUtil::class);
        $containerUtil->method('isFrontend')->willReturn(true);
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getParentRequest')->willReturn(new Request());
        $instance = $this->createTestInstance([
            'containerUtil' => $containerUtil,
            'requestStack' => $requestStack,
        ]);
        $this->assertFalse($instance->isEnabledOnCurrentPage());

        $containerUtil = $this->createMock(ContainerUtil::class);
        $containerUtil->method('isFrontend')->willReturn(true);
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getParentRequest')->willReturn(null);
        $instance = $this->createTestInstance([
            'containerUtil' => $containerUtil,
            'requestStack' => $requestStack,
        ]);
        $pageModel = $this->mockClassWithProperties(PageModel::class, []);
        $this->assertFalse($instance->isEnabledOnCurrentPage($pageModel));

        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findModelInstanceByPk')->willReturnCallback(function ($table, $id) {
            switch ($id) {
               case '1':
                   return null;
               case '2':
                   return $this->mockClassWithProperties(LayoutModel::class, ['addEncore' => '']);
               case '3':
                   return $this->mockClassWithProperties(LayoutModel::class, ['addEncore' => '1']);
           }
        });
        $instance = $this->createTestInstance([
            'containerUtil' => $containerUtil,
            'requestStack' => $requestStack,
            'modelUtil' => $modelUtil,
        ]);
        $pageModel = $this->mockClassWithProperties(PageModel::class, ['layoutId' => 2]);
        $this->assertFalse($instance->isEnabledOnCurrentPage($pageModel));

        $pageModel = $this->mockClassWithProperties(PageModel::class, ['layoutId' => 3]);
        $this->assertTrue($instance->isEnabledOnCurrentPage($pageModel));

        $this->assertFalse($instance->isEnabledOnCurrentPage());

        $GLOBALS['objPage'] = $this->mockClassWithProperties(PageModel::class, ['layoutId' => 3]);
        $this->assertTrue($instance->isEnabledOnCurrentPage());

        unset($GLOBALS['objPage']);
    }

    public function testGetRelativeOutputPath()
    {
        $instance = $this->createTestInstance([
            'bundleConfig' => ['outputPath' => ''],
        ]);
        $this->assertEmpty($instance->getRelativeOutputPath());

        $instance = $this->createTestInstance([
            'bundleConfig' => ['outputPath' => '/a/b/c/d'],
            'webDir' => '/a/b/c',
        ]);
        $this->assertSame('d', $instance->getRelativeOutputPath());
    }

    public function testGetAbsoluteOutputPath()
    {
        $instance = $this->createTestInstance([
            'bundleConfig' => ['outputPath' => ''],
        ]);
        $this->assertEmpty($instance->getAbsoluteOutputPath());

        $instance = $this->createTestInstance([
            'bundleConfig' => ['outputPath' => '/a/b/c/d'],
            'webDir' => '/a/b/c',
        ]);
        $this->assertSame('/a/b/c/d', $instance->getAbsoluteOutputPath());
    }
}
