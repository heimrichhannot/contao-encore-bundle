<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\EventListener;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\EventListener\ReplaceDynamicScriptTagsListener;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use PHPUnit\Framework\MockObject\MockObject;

class ReplaceDynamicScriptTagsListenerTest extends ContaoTestCase
{
    public function testInvoke()
    {
        /** @var ContainerUtil|MockObject $containerUtilMock */
        $containerUtilMock = $this->createMock(ContainerUtil::class);
        $containerUtilMock->expects($this->once())->method('isFrontend')->willReturn(false);

        $modelUtilMock = $this->createMock(ModelUtil::class);

        /** @var ReplaceDynamicScriptTagsListener|MockObject $instance */
        $instance = $this->getMockBuilder(ReplaceDynamicScriptTagsListener::class)
            ->setConstructorArgs([[], $containerUtilMock, $modelUtilMock])
            ->setMethods(['cleanGlobalArrays'])
            ->getMock()
        ;
        $instance->expects($this->never())->method('cleanGlobalArrays')->willReturn(false);
        $instance->__invoke('test');

        /** @var ContainerUtil|MockObject $containerUtilMock */
        $containerUtilMock = $this->createMock(ContainerUtil::class);
        $containerUtilMock->method('isFrontend')->willReturn(true);

        /** @var ModelUtil|MockObject $modelUtilMock */
        $modelUtilMock = $this->createMock(ModelUtil::class);
        $modelUtilMock->method('findModelInstanceByPk')->willReturnOnConsecutiveCalls(
            null,
            $this->mockClassWithProperties(LayoutModel::class, ['addEncore' => '']),
            $this->mockClassWithProperties(LayoutModel::class, ['addEncore' => '1'])
        );

        /** @var ReplaceDynamicScriptTagsListener|MockObject $instance */
        $instance = $this->getMockBuilder(ReplaceDynamicScriptTagsListener::class)
            ->setConstructorArgs([[], $containerUtilMock, $modelUtilMock])
            ->setMethods(['cleanGlobalArrays'])
            ->getMock()
        ;

        $GLOBALS['objPage'] = $this->mockClassWithProperties(PageModel::class, ['layoutId' => 1]);

        $instance->expects($this->once())->method('cleanGlobalArrays')->willReturn(true);
        $instance->__invoke('test');
        $instance->__invoke('test');
        $instance->__invoke('test');
    }
}
