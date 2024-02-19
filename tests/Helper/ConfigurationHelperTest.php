<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Helper;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConfigurationHelperTest extends ContaoTestCase
{
    public function createTestInstance(array $parameters = [])
    {
        $requestStack = $parameters['requestStack'] ?? $this->createMock(RequestStack::class);
        $parameterBag = $parameters['parameterBag'] ?? new ParameterBag([]);
        $scopeMatcher = $parameters['scopeMatcher'] ?? $this->createMock(ScopeMatcher::class);

        if (!isset($parameters['eventDispatcher'])) {
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
            $eventDispatcher->method('dispatch')->willReturnArgument(0);
        } else {
            $eventDispatcher = $parameters['eventDispatcher'];
        }

        $contaoFramework = $parameters['contaoFramework'] ?? $this->mockContaoFramework([
            LayoutModel::class => $this->mockAdapter(['findByPk']),
            PageModel::class => $this->mockAdapter(['findByPk']),
        ]);

        $instance = new ConfigurationHelper(
            $requestStack,
            $parameterBag,
            $scopeMatcher,
            $contaoFramework,
            $eventDispatcher,
        );

        return $instance;
    }

    public function testIsEnabledOnCurrentPage()
    {
        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher->method('isFrontendRequest')->willReturn(false);
        $instance = $this->createTestInstance([
            'scopeMatcher' => $scopeMatcher,
        ]);
        $this->assertFalse($instance->isEnabledOnCurrentPage());

        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher->method('isFrontendRequest')->willReturn(true);
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getParentRequest')->willReturn(new Request());
        $instance = $this->createTestInstance([
            'scopeMatcher' => $scopeMatcher,
            'requestStack' => $requestStack,
        ]);
        $this->assertFalse($instance->isEnabledOnCurrentPage());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getParentRequest')->willReturn(null);
        $requestStack->method('getCurrentRequest')->willReturn(new Request());
        $instance = $this->createTestInstance([
            'scopeMatcher' => $scopeMatcher,
            'requestStack' => $requestStack,
        ]);
        $pageModel = $this->mockClassWithProperties(PageModel::class, []);
        $this->assertFalse($instance->isEnabledOnCurrentPage($pageModel));

        $layoutModelAdapter = $this->mockAdapter(['findByPk']);
        $layoutModelAdapter->method('findByPk')->willReturnCallback(function ($id) {
            switch ($id) {
               case '1':
                   return null;
               case '2':
                   return $this->mockClassWithProperties(LayoutModel::class, ['addEncore' => '']);
               case '3':
                   return $this->mockClassWithProperties(LayoutModel::class, ['addEncore' => '1']);
           }

            return null;
        });
        $contaoFramework = $this->mockContaoFramework([
            LayoutModel::class => $layoutModelAdapter,
        ]);

        $instance = $this->createTestInstance([
            'scopeMatcher' => $scopeMatcher,
            'requestStack' => $requestStack,
            'contaoFramework' => $contaoFramework,
        ]);
        $pageModel = $this->mockClassWithProperties(PageModel::class, ['layoutId' => 2]);
        $this->assertFalse($instance->isEnabledOnCurrentPage($pageModel));

        $pageModel = $this->mockClassWithProperties(PageModel::class, ['layoutId' => 3]);
        $this->assertTrue($instance->isEnabledOnCurrentPage($pageModel));

        $this->assertFalse($instance->isEnabledOnCurrentPage());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getParentRequest')->willReturn(null);
        $requestStack->method('getCurrentRequest')->willReturn(new Request([], [], [
            'pageModel' => $this->mockClassWithProperties(PageModel::class, ['layoutId' => 3]),
        ]));

        $instance = $this->createTestInstance([
            'scopeMatcher' => $scopeMatcher,
            'requestStack' => $requestStack,
            'contaoFramework' => $contaoFramework,
        ]);
        $this->assertTrue($instance->isEnabledOnCurrentPage());

        unset($GLOBALS['objPage']);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(new Request());
        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher->method('isFrontendRequest')->willReturn(false);

        $instance = $this->createTestInstance([
            'scopeMatcher' => $scopeMatcher,
            'requestStack' => $requestStack,
        ]);
        $this->assertFalse($instance->isEnabledOnCurrentPage());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(new Request());
        $requestStack->method('getParentRequest')->willReturn(new Request());
        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher->method('isFrontendRequest')->willReturn(true);

        $instance = $this->createTestInstance([
            'scopeMatcher' => $scopeMatcher,
            'requestStack' => $requestStack,
        ]);
        $this->assertFalse($instance->isEnabledOnCurrentPage());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(new Request([], [], [
            'pageModel' => $this->mockClassWithProperties(PageModel::class, [
                'layoutId' => 3,
                'type' => 'error_404',
            ]),
        ]));
        $requestStack->method('getParentRequest')->willReturn(new Request());
        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher->method('isFrontendRequest')->willReturn(true);

        $instance = $this->createTestInstance([
            'scopeMatcher' => $scopeMatcher,
            'requestStack' => $requestStack,
            'contaoFramework' => $contaoFramework,
        ]);
        $this->assertTrue($instance->isEnabledOnCurrentPage());
    }

    public function testGetRelativeOutputPath()
    {
        $parameterBag = new ParameterBag([
            'huh_encore' => ['outputPath' => ''],
        ]);

        $instance = $this->createTestInstance([
            'parameterBag' => $parameterBag,
        ]);
        $this->assertEmpty($instance->getRelativeOutputPath());

        $parameterBag->set('huh_encore', ['outputPath' => '/a/b/c/d']);
        $parameterBag->set('contao.web_dir', '/a/b/c');

        $instance = $this->createTestInstance([
            'parameterBag' => $parameterBag,
        ]);
        $this->assertSame('d', $instance->getRelativeOutputPath());
    }

    public function testGetAbsoluteOutputPath()
    {
        $parameterBag = new ParameterBag([
            'huh_encore' => ['outputPath' => ''],
        ]);

        $instance = $this->createTestInstance([
            'parameterBag' => $parameterBag,
        ]);
        $this->assertEmpty($instance->getAbsoluteOutputPath());

        $parameterBag->set('huh_encore', ['outputPath' => '/a/b/c/d']);
        $parameterBag->set('contao.web_dir', '/a/b/c');

        $instance = $this->createTestInstance([
            'parameterBag' => $parameterBag,
        ]);
        $this->assertSame('/a/b/c/d', $instance->getAbsoluteOutputPath());
    }

    public function testGetPageModel()
    {
        $instance = $this->createTestInstance();

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(null);
        $instance = $this->createTestInstance([
            'requestStack' => $requestStack,
        ]);
        $this->assertNull($instance->getPageModel());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(new Request([], [], [
            'pageModel' => $this->mockClassWithProperties(PageModel::class, ['layoutId' => 3]),
        ]));
        $instance = $this->createTestInstance([
            'requestStack' => $requestStack,
        ]);
        $this->assertSame(3, $instance->getPageModel()->layoutId);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(new Request([], [], [
            'pageModel' => 3,
        ]));
        $instance = $this->createTestInstance([
            'requestStack' => $requestStack,
        ]);
        $this->assertNull($instance->getPageModel());

        $pageModelAdapter = $this->mockAdapter(['findByPk']);
        $pageModelAdapter->method('findByPk')->willReturnCallback(function ($id) {
            switch ($id) {
                case '3':
                    return $this->mockClassWithProperties(PageModel::class, ['layoutId' => 3]);
                default:
                    return null;
            }
        });

        $contaoFramework = $this->mockContaoFramework([
            PageModel::class => $pageModelAdapter,
        ]);

        $GLOBALS['objPage'] = $this->mockClassWithProperties(PageModel::class, ['id' => 2, 'layoutId' => 2]);

        $instance = $this->createTestInstance([
            'requestStack' => $requestStack,
            'contaoFramework' => $contaoFramework,
        ]);

        $this->assertSame(3, $instance->getPageModel()->layoutId);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(new Request([], [], [
            'pageModel' => 2,
        ]));

        $instance = $this->createTestInstance([
            'requestStack' => $requestStack,
            'contaoFramework' => $contaoFramework,
        ]);

        $this->assertSame(2, $instance->getPageModel()->layoutId);
    }
}
