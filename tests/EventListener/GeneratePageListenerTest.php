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
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
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
        $hookListener = $this->createTestInstance([], $this->getMockBuilder(GeneratePageListener::class)->setMethods(['addEncore', 'cleanGlobalArrays']));
        $hookListener->expects($this->never())->method('addEncore')->willReturn(true);

        $pageModel = $this->mockClassWithProperties(PageModel::class, []);
        $layoutModel = $this->mockModelObject(LayoutModel::class, ['addEncore' => '']);
        $pageRegular = $this->createMock(PageRegular::class);
        $hookListener->__invoke($pageModel, $layoutModel, $pageRegular);
        unset($hookListener);

        $hookListener = $this->createTestInstance([], $this->getMockBuilder(GeneratePageListener::class)->setMethods(['addEncore']));
        $hookListener->expects($this->once())->method('addEncore')->willReturn(true);

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

    public function testCleanGlobalArrays()
    {
        // Not frontend
        $container = $this->mockContainer();
        $containerUtilMock = $this->createMock(ContainerUtil::class);
        $containerUtilMock->expects($this->once())->method('isFrontend')->willReturn(false);
        $container->set('huh.utils.container', $containerUtilMock);
        $listener = $this->createTestInstance(['container' => $container]);
        $layout = $this->mockModelObject(LayoutModel::class);
        $listener->cleanGlobalArrays($layout);

        // Frontend
        $container = $this->mockContainer();
        $containerUtilMock = $this->createMock(ContainerUtil::class);
        $containerUtilMock->method('isFrontend')->willReturn(true);
        $container->set('huh.utils.container', $containerUtilMock);
        $listener = $this->createTestInstance([
            'bundleConfig' => [
                'unset_global_keys' => [
                    'js' => [],
                    'jquery' => [],
                    'javascript' => [],
                ],
                'unset_jquery' => false,
            ],
            'container' => $container,
            ]);
        /** @var LayoutModel $layout */
        $layout = $this->mockModelObject(LayoutModel::class, ['addEncore' => '']);
        $listener->cleanGlobalArrays($layout);

        $GLOBALS['TL_JAVASCRIPT'] = ['assets/jquery/js/jquery.min.js|static', 'contao-a-bundle' => '', 'contao-b-bundle' => ''];
        $GLOBALS['TL_JQUERY'] = ['contao-a-bundle' => '', 'contao-c-bundle' => '', 'contao-jquery-bundle' => '', 'contao-d-bundle' => '', 'contao-e-bundle' => ''];
        $GLOBALS['TL_USER_CSS'] = ['contao-a-bundle' => '', 'contao-b-bundle' => '', 'contao-jquery-bundle' => ''];
        $GLOBALS['TL_CSS'] = ['contao-a-bundle' => '', 'contao-c-bundle' => '', 'contao-css-bundle' => ''];
        $listener = $this->createTestInstance([
            'bundleConfig' => [
                'unset_global_keys' => [
                    'js' => ['contao-b-bundle'],
                    'jquery' => ['contao-b-bundle', 'contao-a-bundle', 'contao-jquery-bundle'],
                    'css' => ['contao-a-bundle', 'contao-css-bundle'],
                ],
                'unset_jquery' => false,
            ],
            'container' => $container,
            ]);
        /** @var LayoutModel $layout */
        $layout = $this->mockModelObject(LayoutModel::class, ['addEncore' => '1']);
        $listener->cleanGlobalArrays($layout);
        $this->assertCount(2, $GLOBALS['TL_JAVASCRIPT']);
        $this->assertCount(3, $GLOBALS['TL_JQUERY']);
        $this->assertCount(2, $GLOBALS['TL_USER_CSS']);
        $this->assertCount(1, $GLOBALS['TL_CSS']);

        $listener = $this->createTestInstance([
            'bundleConfig' => [
                'unset_global_keys' => [
                    'js' => ['contao-b-bundle'],
                    'jquery' => ['contao-b-bundle', 'contao-a-bundle', 'contao-jquery-bundle'],
                    'css' => ['contao-a-bundle', 'contao-css-bundle'],
                ],
                'unset_jquery' => true,
            ],
            'container' => $container,
        ]);
        $GLOBALS['TL_JAVASCRIPT'] = ['assets/jquery/js/jquery.min.js|static', 'contao-a-bundle' => '', 'contao-b-bundle' => ''];
        $GLOBALS['TL_JQUERY'] = ['contao-a-bundle' => '', 'contao-c-bundle' => '', 'contao-jquery-bundle' => '', 'contao-d-bundle' => ''];
        $GLOBALS['TL_USER_CSS'] = ['contao-a-bundle' => '', 'contao-b-bundle' => '', 'contao-jquery-bundle' => ''];
        $GLOBALS['TL_CSS'] = ['contao-a-bundle' => '', 'contao-c-bundle' => '', 'contao-css-bundle' => ''];
        $listener->cleanGlobalArrays($layout);
        $this->assertCount(1, $GLOBALS['TL_JAVASCRIPT']);
        $this->assertCount(2, $GLOBALS['TL_JQUERY']);
        $this->assertCount(2, $GLOBALS['TL_USER_CSS']);
        $this->assertCount(1, $GLOBALS['TL_CSS']);
    }
}
