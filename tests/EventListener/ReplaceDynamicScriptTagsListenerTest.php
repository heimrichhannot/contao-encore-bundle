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
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use HeimrichHannot\EncoreBundle\EventListener\ReplaceDynamicScriptTagsListener;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;

class ReplaceDynamicScriptTagsListenerTest extends ContaoTestCase
{
    /**
     * @return ReplaceDynamicScriptTagsListener|MockObject
     */
    public function getTestInstance(array $parameter = [], ?MockBuilder $mockBuilder = null)
    {
        $parameter['bundleConfig'] = $parameter['bundleConfig'] ?? [];
        $parameter['modelUtil'] = $parameter['modelUtil'] ?? $this->createMock(ModelUtil::class);
        $parameter['templateAsset'] = $parameter['templateAsset'] ?? $this->createMock(TemplateAsset::class);
        $parameter['configurationHelper'] = $parameter['configurationHelper'] ?? $this->createMock(ConfigurationHelper::class);

        if ($mockBuilder) {
            $instance = $mockBuilder->setConstructorArgs([
                $parameter['bundleConfig'],
                $parameter['modelUtil'],
                $parameter['templateAsset'],
                $parameter['configurationHelper'],
            ])->getMock();
        } else {
            $instance = new ReplaceDynamicScriptTagsListener(
                $parameter['bundleConfig'],
                $parameter['modelUtil'],
                $parameter['templateAsset'],
                $parameter['configurationHelper']
            );
        }

        return $instance;
    }

    public function testInvoke()
    {
        // Encore not enabled
        $configurationHelper = $this->createMock(ConfigurationHelper::class);
        $configurationHelper->method('isEnabledOnCurrentPage')->willReturn(false);

        /** @var ReplaceDynamicScriptTagsListener|MockObject $instance */
        $mockBilder = $this->getMockBuilder(ReplaceDynamicScriptTagsListener::class)
            ->setMethods(['cleanGlobalArrays', 'replaceEncoreTags']);
        $instance = $this->getTestInstance([
            'configurationHelper' => $configurationHelper,
        ], $mockBilder);

        $instance->expects($this->never())->method('cleanGlobalArrays')->willReturn(false);
        $instance->expects($this->never())->method('replaceEncoreTags')->willReturn(false);
        $instance->__invoke('test');

        //Encore enabled
        $configurationHelper = $this->createMock(ConfigurationHelper::class);
        $configurationHelper->method('isEnabledOnCurrentPage')->willReturn(true);

        /** @var ModelUtil|MockObject $modelUtilMock */
        $modelUtilMock = $this->createMock(ModelUtil::class);
        $modelUtilMock->method('findModelInstanceByPk')->willReturnOnConsecutiveCalls(
            null,
            $this->mockClassWithProperties(LayoutModel::class, ['addEncore' => '1'])
        );

        /** @var ReplaceDynamicScriptTagsListener|MockObject $instance */
        $mockBilder = $this->getMockBuilder(ReplaceDynamicScriptTagsListener::class)
            ->setMethods(['cleanGlobalArrays', 'replaceEncoreTags']);
        $instance = $this->getTestInstance([
            'configurationHelper' => $configurationHelper,
            'modelUtil' => $modelUtilMock,
        ], $mockBilder);

        $GLOBALS['objPage'] = $this->mockClassWithProperties(PageModel::class, ['layoutId' => 1]);

        $instance->expects($this->once())->method('cleanGlobalArrays')->willReturn(true);
        $instance->__invoke('test');
        $instance->__invoke('test');
    }

    public function testReplaceEncoreTags()
    {
        $configurationHelper = $this->createMock(ConfigurationHelper::class);
        $configurationHelper->method('isEnabledOnCurrentPage')->willReturn(true);

        $modelUtilMock = $this->createMock(ModelUtil::class);
        $modelUtilMock->method('findModelInstanceByPk')->willReturn(
            $this->mockClassWithProperties(LayoutModel::class, ['addEncore' => '1'])
        );

        $templateAssetMock = $this->createMock(TemplateAsset::class);
        $templateAssetMock->method('createInstance')->willReturnSelf();
        $templateAssetMock->method('linkTags')->willReturn('<link>');
        $templateAssetMock->method('scriptTags')->willReturn('<script>');
        $templateAssetMock->method('headScriptTags')->willReturn('<head>');

        $mockBilder = $this->getMockBuilder(ReplaceDynamicScriptTagsListener::class)
            ->setMethods(['cleanGlobalArrays']);

        $instance = $this->getTestInstance([
            'configurationHelper' => $configurationHelper,
            'modelUtil' => $modelUtilMock,
            'templateAsset' => $templateAssetMock,
        ], $mockBilder);
        $instance->method('cleanGlobalArrays')->willReturn(null);

        $GLOBALS['objPage'] = $this->mockClassWithProperties(PageModel::class, ['layoutId' => 1]);

        $this->assertSame('Hallo', $instance->__invoke('Hallo'));
        $this->assertSame('Hallo <link>', $instance->__invoke('Hallo [[HUH_ENCORE_CSS]]'));
        $this->assertSame('<script> Hallo', $instance->__invoke('[[HUH_ENCORE_JS]] Hallo'));
        $this->assertSame('<head>Hallo', $instance->__invoke('[[HUH_ENCORE_HEAD_JS]]Hallo'));
        $this->assertSame('<head>Ha<script>llo <link>', $instance->__invoke('[[HUH_ENCORE_HEAD_JS]]Ha[[HUH_ENCORE_JS]]llo [[HUH_ENCORE_CSS]]'));
        $this->assertSame(
            '<head>Ha<script>llo <link> [[TL_CSS]]',
            $instance->__invoke('[[HUH_ENCORE_HEAD_JS]]Ha[[HUH_ENCORE_JS]]llo [[HUH_ENCORE_CSS]] [[TL_CSS]]'));
    }

    public function testReplaceContaoTags()
    {
        $configurationHelper = $this->createMock(ConfigurationHelper::class);
        $configurationHelper->method('isEnabledOnCurrentPage')->willReturn(true);

        $modelUtilMock = $this->createMock(ModelUtil::class);
        $modelUtilMock->method('findModelInstanceByPk')->willReturn(
            $this->mockClassWithProperties(LayoutModel::class, ['addEncore' => '1'])
        );

        $templateAssetMock = $this->createMock(TemplateAsset::class);
        $templateAssetMock->method('createInstance')->willReturnSelf();
        $templateAssetMock->method('linkTags')->willReturn('<link>');
        $templateAssetMock->method('scriptTags')->willReturn('<script>');
        $templateAssetMock->method('headScriptTags')->willReturn('<head>');

        $mockBilder = $this->getMockBuilder(ReplaceDynamicScriptTagsListener::class)
            ->setMethods(['cleanGlobalArrays']);

        $instance = $this->getTestInstance([
            'bundleConfig' => ['use_contao_template_variables' => true],
            'configurationHelper' => $configurationHelper,
            'modelUtil' => $modelUtilMock,
            'templateAsset' => $templateAssetMock,
        ], $mockBilder);
        $instance->method('cleanGlobalArrays')->willReturn(null);

        $GLOBALS['objPage'] = $this->mockClassWithProperties(PageModel::class, ['layoutId' => 1]);

        $this->assertSame('Hallo', $instance->__invoke('Hallo'));
        $this->assertSame('Hallo [[TL_CSS]]<link>', $instance->__invoke('Hallo [[TL_CSS]]'));
        $this->assertSame('<script>[[TL_BODY]] Hallo', $instance->__invoke('[[TL_BODY]] Hallo'));
        $this->assertSame('<head>[[TL_HEAD]]Hallo', $instance->__invoke('[[TL_HEAD]]Hallo'));
        $this->assertSame('<head>[[TL_HEAD]]Ha<script>[[TL_BODY]]llo [[TL_CSS]]<link>', $instance->__invoke('[[TL_HEAD]]Ha[[TL_BODY]]llo [[TL_CSS]]'));
        $this->assertSame(
            '<head>[[TL_HEAD]]Ha<script>[[TL_BODY]]llo [[TL_CSS]]<link> [[HUH_ENCORE_CSS]]',
            $instance->__invoke('[[TL_HEAD]]Ha[[TL_BODY]]llo [[TL_CSS]] [[HUH_ENCORE_CSS]]'));
    }
}
