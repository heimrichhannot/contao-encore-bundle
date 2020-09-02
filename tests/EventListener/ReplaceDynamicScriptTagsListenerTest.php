<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\EncoreBundle\Test\EventListener;


use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\EventListener\ReplaceDynamicScriptTagsListener;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use PHPUnit\Framework\MockObject\MockObject;

class ReplaceDynamicScriptTagsListenerTest extends ContaoTestCase
{
    public function testInvoke()
    {
        /** @var ContainerUtil|MockObject $containerUtilMock */
        $containerUtilMock = $this->createMock(ContainerUtil::class);
        $containerUtilMock->expects($this->once())->method('isFrontend')->willReturn(false);

        /** @var ReplaceDynamicScriptTagsListener|MockObject $instance */
        $instance = $this->getMockBuilder(ReplaceDynamicScriptTagsListener::class)
            ->setConstructorArgs([[], $containerUtilMock])
            ->setMethods(['cleanGlobalArrays'])
            ->getMock()
        ;
        $instance->expects($this->never())->method('cleanGlobalArrays')->willReturn(false);
        $instance->__invoke("test");

        /** @var ContainerUtil|MockObject $containerUtilMock */
        $containerUtilMock = $this->createMock(ContainerUtil::class);
        $containerUtilMock->expects($this->once())->method('isFrontend')->willReturn(true);

        /** @var ReplaceDynamicScriptTagsListener|MockObject $instance */
        $instance = $this->getMockBuilder(ReplaceDynamicScriptTagsListener::class)
            ->setConstructorArgs([[], $containerUtilMock])
            ->setMethods(['cleanGlobalArrays'])
            ->getMock()
        ;
        $instance->expects($this->once())->method('cleanGlobalArrays')->willReturn(true);
        $instance->__invoke("test");

        return;
    }
}