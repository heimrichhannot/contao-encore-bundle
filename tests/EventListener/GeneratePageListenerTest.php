<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\EventListener;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use HeimrichHannot\EncoreBundle\EventListener\GeneratePageListener;
use HeimrichHannot\EncoreBundle\Test\ModelMockTrait;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Twig\Environment;

class GeneratePageListenerTest extends ContaoTestCase
{
    use ModelMockTrait;

    /**
     * @param MockBuilder $hookListenerMock
     *
     * @return GeneratePageListener|MockObject
     */
    public function createTestInstance(array $parameters = [], $hookListenerMock = null)
    {
        if (!isset($parameters['bundleConfig'])) {
            $parameters['bundleConfig'] = [];
        }
        if (!isset($parameters['container'])) {
            $parameters['container'] = $this->mockContainer();
        }
        if (!isset($parameters['framework'])) {
            $parameters['framework'] = $this->mockContaoFramework();
        }
        if (!isset($parameters['twig'])) {
            /** @var Environment|MockObject $twig */
            $twig = $this->createMock(Environment::class);
            $twig->method('render')->willReturnArgument(1);
            $parameters['twig'] = $twig;
        }
        if (!isset($parameters['templateAsset'])) {
            /** @var TemplateAsset|MockObject $templateAsset */
            $templateAsset = $this->createMock(TemplateAsset::class);
            $templateAsset->method('createInstance')->willReturnSelf();
            $templateAsset->method('linkTags')->willReturn('');
            $templateAsset->method('inlineCssLinkTag')->willReturn('');
            $templateAsset->method('scriptTags')->willReturn('');
            $templateAsset->method('headScriptTags')->willReturn('');
            $parameters['templateAsset'] = $templateAsset;
        }

        if (!$hookListenerMock) {
            $hookListener = new GeneratePageListener(
                $parameters['bundleConfig'],
                $parameters['framework'],
                $parameters['container'],
                $parameters['twig'],
                $parameters['templateAsset']
            );
        } else {
            $hookListener = $hookListenerMock->setConstructorArgs([
                $parameters['bundleConfig'],
                $parameters['framework'],
                $parameters['container'],
                $parameters['twig'],
                $parameters['templateAsset'],
            ])->getMock();
        }

        return $hookListener;
    }

    public function testInvoke()
    {
        $hookListener = $this->createTestInstance([], $this->getMockBuilder(GeneratePageListener::class)->setMethods(['createEncoreScriptTags']));
        $hookListener->expects($this->never())->method('createEncoreScriptTags')->willReturn(true);

        $pageModel = $this->mockClassWithProperties(PageModel::class, []);
        $layoutModel = $this->mockModelObject(LayoutModel::class, ['addEncore' => '']);
        $pageRegular = $this->createMock(PageRegular::class);
        $hookListener->__invoke($pageModel, $layoutModel, $pageRegular);
        unset($hookListener);

        $hookListener = $this->createTestInstance([], $this->getMockBuilder(GeneratePageListener::class)->setMethods(['createEncoreScriptTags']));
        $hookListener->expects($this->once())->method('createEncoreScriptTags')->willReturn(true);

        $pageModel = $this->mockClassWithProperties(PageModel::class, []);
        $layoutModel = $this->mockClassWithProperties(LayoutModel::class, ['addEncore' => '1']);
        $pageRegular = $this->createMock(PageRegular::class);
        $hookListener->__invoke($pageModel, $layoutModel, $pageRegular);
    }

    public function testAddEncore()
    {
        /** @var TemplateAsset|MockObject $templateAsset */
        $templateAsset = $this->createMock(TemplateAsset::class);
        $templateAsset->expects($this->exactly(2))->method('createInstance')->willReturnSelf();
        $templateAsset->method('linkTags')->willReturn('<link rel="stylesheet" href="/build/anwaltverein-theme.css">');
        $templateAsset->method('inlineCssLinkTag')->willReturn('<styles>a.custom{color:blue;}</styles>');
        $templateAsset->method('scriptTags')->willReturn('<script src="/build/contao-slick-bundle.bundle.js"></script>');
        $templateAsset->method('headScriptTags')->willReturn('<script src="/build/contao-head-bundle.bundle.js"></script>');
        $pageModel = $this->mockModelObject(PageModel::class);
        $layoutModel = $this->mockModelObject(LayoutModel::class, []);
        $pageRegular = $this->mockClassWithProperties(PageRegular::class, ['Template' => new \stdClass()]);
        $listener = $this->createTestInstance(['templateAsset' => $templateAsset]);

        $listener->addEncore($pageModel, $layoutModel, $pageRegular);
        $this->assertSame('<link rel="stylesheet" href="/build/anwaltverein-theme.css">', $pageRegular->Template->encoreStylesheets);
        $this->assertFalse(isset($pageRegular->Template->encoreStylesheetsInline));

        $listener->addEncore($pageModel, $layoutModel, $pageRegular, null, true);
        $this->assertSame('<styles>a.custom{color:blue;}</styles>', $pageRegular->Template->encoreStylesheetsInline);
    }

    public function testCreateEncoreScriptTags()
    {
        /** @var GeneratePageListener $instance */
        $instance = $this->createTestInstance();

        $pageModel = $this->mockClassWithProperties(PageModel::class, []);
        $layoutModel = $this->mockModelObject(LayoutModel::class, ['addEncore' => '1']);
        $pageRegular = $this->mockClassWithProperties(PageRegular::class, ['Template' => new \stdClass()]);

        $instance->__invoke($pageModel, $layoutModel, $pageRegular);

        $this->assertSame('[[HUH_ENCORE_CSS]]', $pageRegular->Template->encoreStylesheets);
        $this->assertSame('[[HUH_ENCORE_JS]]', $pageRegular->Template->encoreScripts);
        $this->assertSame('[[HUH_ENCORE_HEAD_JS]]', $pageRegular->Template->encoreHeadScripts);
    }
}
