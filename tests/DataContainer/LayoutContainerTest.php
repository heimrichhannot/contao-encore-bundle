<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\DataContainer;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\LayoutModel;
use Contao\Message;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\DataContainer\LayoutContainer;
use HeimrichHannot\UtilsBundle\Util\Utils;
use PHPUnit\Framework\MockObject\MockObject;

class LayoutContainerTest extends ContaoTestCase
{
    public function testOnLoadCallback()
    {
        $GLOBALS['TL_LANG']['tl_layout']['INFO']['jquery_order_conflict'] = 'Info';
        $bundleConfig = [];

        $messageAdapter = $this->mockAdapter(['addInfo']);
        $messageAdapter->expects($this->never())->method('addInfo')->willReturn(null);

        /** @var ContaoFramework|MockObject $contaoFramework */
        $contaoFramework = $this->mockContaoFramework([
            Message::class => $messageAdapter,
        ]);

        $utils = $this->createMock(Utils::class);

        $instance = new LayoutContainer($bundleConfig, $utils, $contaoFramework);
        $instance->onLoadCallback(null);

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $instance->onLoadCallback($dc);

        $instance = new LayoutContainer($bundleConfig, $utils, $contaoFramework);
        $instance->onLoadCallback($dc);

        $bundleConfig['use_contao_template_variables'] = false;
        $instance = new LayoutContainer($bundleConfig, $utils, $contaoFramework);
        $instance->onLoadCallback($dc);

        $bundleConfig['use_contao_template_variables'] = true;
        $instance = new LayoutContainer($bundleConfig, $utils, $contaoFramework);
        $instance->onLoadCallback($dc);

        $instance = new LayoutContainer($bundleConfig, $utils, $contaoFramework);
        $instance->onLoadCallback($dc);

        $utils = $this->createMock(Utils::class);
        $utils->method('container')->willReturnCallback(function () {
            $container = $this->createMock(\HeimrichHannot\UtilsBundle\Util\Container\ContainerUtil::class);
            $container->method('isBackend')->willReturn(true);

            return $container;
        });

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
        $instance = new LayoutContainer($bundleConfig, $utils, $contaoFramework);
        $instance->onLoadCallback($dc);

        $bundleConfig['unset_jquery'] = true;
        $messageAdapter = $this->mockAdapter(['addInfo']);
        $messageAdapter->expects($this->never())->method('addInfo')->willReturn(null);
        /** @var ContaoFramework|MockObject $contaoFramework */
        $contaoFramework = $this->mockContaoFramework([
            Message::class => $messageAdapter,
            LayoutModel::class => $layoutModel,
        ]);
        $instance = new LayoutContainer($bundleConfig, $utils, $contaoFramework);
        $instance->onLoadCallback($dc);
    }
}
