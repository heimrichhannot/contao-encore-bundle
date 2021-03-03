<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
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
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use PHPUnit\Framework\MockObject\MockObject;

class LayoutContainerTest extends ContaoTestCase
{
    public function testOnLoadCallback()
    {
        $GLOBALS['TL_LANG']['tl_layout']['INFO']['jquery_order_conflict'] = 'Info';
        $bundleConfig = [];
        $containerUtil = $this->createMock(ContainerUtil::class);
        $modelUtil = $this->createMock(ModelUtil::class);

        $messageAdapter = $this->mockAdapter(['addInfo']);
        $messageAdapter->expects($this->never())->method('addInfo')->willReturn(null);

        /** @var ContaoFramework|MockObject $contaoFramework */
        $contaoFramework = $this->mockContaoFramework([
            Message::class => $messageAdapter,
        ]);

        $instance = new LayoutContainer($bundleConfig, $containerUtil, $modelUtil, $contaoFramework);
        $instance->onLoadCallback(null);

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $instance->onLoadCallback($dc);

        $containerUtil->method('isBackend')->willReturn(true);
        $instance = new LayoutContainer($bundleConfig, $containerUtil, $modelUtil, $contaoFramework);
        $instance->onLoadCallback($dc);

        $bundleConfig['use_contao_template_variables'] = false;
        $instance = new LayoutContainer($bundleConfig, $containerUtil, $modelUtil, $contaoFramework);
        $instance->onLoadCallback($dc);

        $bundleConfig['use_contao_template_variables'] = true;
        $instance = new LayoutContainer($bundleConfig, $containerUtil, $modelUtil, $contaoFramework);
        $instance->onLoadCallback($dc);

        $modelUtil->method('findModelInstanceByPk')->willReturn($this->mockClassWithProperties(LayoutModel::class, []));
        $instance = new LayoutContainer($bundleConfig, $containerUtil, $modelUtil, $contaoFramework);
        $instance->onLoadCallback($dc);

        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findModelInstanceByPk')->willReturn($this->mockClassWithProperties(LayoutModel::class, [
            'addEncore' => true,
            'addJQuery' => true,
        ]));
        $messageAdapter = $this->mockAdapter(['addInfo']);
        $messageAdapter->expects($this->once())->method('addInfo')->willReturn(null);
        /** @var ContaoFramework|MockObject $contaoFramework */
        $contaoFramework = $this->mockContaoFramework([
            Message::class => $messageAdapter,
        ]);
        $instance = new LayoutContainer($bundleConfig, $containerUtil, $modelUtil, $contaoFramework);
        $instance->onLoadCallback($dc);

        $bundleConfig['unset_jquery'] = true;
        $messageAdapter = $this->mockAdapter(['addInfo']);
        $messageAdapter->expects($this->never())->method('addInfo')->willReturn(null);
        /** @var ContaoFramework|MockObject $contaoFramework */
        $contaoFramework = $this->mockContaoFramework([
            Message::class => $messageAdapter,
        ]);
        $instance = new LayoutContainer($bundleConfig, $containerUtil, $modelUtil, $contaoFramework);
        $instance->onLoadCallback($dc);
    }
}
