<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\DataContainer;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\DataContainer;
use Contao\LayoutModel;
use Contao\Message;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\DataContainer\LayoutContainer;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LayoutContainerTest extends ContaoTestCase
{
    public function createTestInstance(array $parameters = []): LayoutContainer
    {
        $bundleConfig = $parameters['bundleConfig'] ?? [];
        $contaoFramework = $parameters['contaoFramework'] ?? $this->mockContaoFramework();
        $requestStack = $parameters['requestStack'] ?? $this->createMock(RequestStack::class);
        $scopeMatcher = $parameters['scopeMatcher'] ?? $this->createMock(ScopeMatcher::class);

        return new LayoutContainer(
            $bundleConfig,
            $contaoFramework,
            $requestStack,
            $scopeMatcher
        );
    }

    public function testOnLoadCallback()
    {
        $GLOBALS['TL_LANG']['tl_layout']['INFO']['jquery_order_conflict'] = 'Info';

        $messageAdapter = $this->mockAdapter(['addInfo']);
        $messageAdapter->expects($this->never())->method('addInfo')->willReturn(null);

        /** @var ContaoFramework|MockObject $contaoFramework */
        $contaoFramework = $this->mockContaoFramework([
            Message::class => $messageAdapter,
        ]);

        $instance = $this->createTestInstance([
            'contaoFramework' => $contaoFramework,
        ]);
        $instance->onLoadCallback(null);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(new Request());

        $instance = $this->createTestInstance([
            'contaoFramework' => $contaoFramework,
            'requestStack' => $requestStack,
        ]);
        $instance->onLoadCallback(null);

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $instance->onLoadCallback($dc);

        $bundleConfig['use_contao_template_variables'] = false;

        $instance = $this->createTestInstance([
            'contaoFramework' => $contaoFramework,
            'bundleConfig' => $bundleConfig,
            'requestStack' => $requestStack,
        ]);
        $instance->onLoadCallback($dc);

        $bundleConfig['use_contao_template_variables'] = true;
        $instance = $this->createTestInstance([
            'contaoFramework' => $contaoFramework,
            'bundleConfig' => $bundleConfig,
            'requestStack' => $requestStack,
        ]);
        $instance->onLoadCallback($dc);

        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher->method('isBackendRequest')->willReturn(true);

        $layoutModel = $this->mockAdapter(['findByPk']);
        $layoutModel->method('findByPk')->willReturn($this->mockClassWithProperties(LayoutModel::class, [
            'addEncore' => true,
            'addJQuery' => true,
        ]));

        $messageAdapter = $this->mockAdapter(['addInfo']);
        $messageAdapter->expects($this->once())->method('addInfo')->willReturn(null);
        /** @var ContaoFramework|MockObject $contaoFramework */
        $contaoFramework = $this->mockContaoFramework([
            Message::class => $messageAdapter,
            LayoutModel::class => $layoutModel,
        ]);
        $instance = $this->createTestInstance([
            'contaoFramework' => $contaoFramework,
            'bundleConfig' => $bundleConfig,
            'scopeMatcher' => $scopeMatcher,
            'requestStack' => $requestStack,
        ]);
        $instance->onLoadCallback($dc);

        $bundleConfig['unset_jquery'] = true;
        $messageAdapter = $this->mockAdapter(['addInfo']);
        $messageAdapter->expects($this->never())->method('addInfo')->willReturn(null);
        /** @var ContaoFramework|MockObject $contaoFramework */
        $contaoFramework = $this->mockContaoFramework([
            Message::class => $messageAdapter,
            LayoutModel::class => $layoutModel,
        ]);
        $instance = $this->createTestInstance([
            'contaoFramework' => $contaoFramework,
            'bundleConfig' => $bundleConfig,
            'scopeMatcher' => $scopeMatcher,
            'requestStack' => $requestStack,
        ]);
        $instance->onLoadCallback($dc);
    }
}
