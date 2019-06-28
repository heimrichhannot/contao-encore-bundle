<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\EventListener;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\EntrypointsJsonLookup;
use HeimrichHannot\EncoreBundle\EventListener\HookListener;
use HeimrichHannot\EncoreBundle\Test\ModelMockTrait;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class HookListenerTest extends ContaoTestCase
{
    use ModelMockTrait;

    /**
     * @return HookListener|MockObject
     */
    public function getHookListener(ContainerBuilder $container = null, MockBuilder $mock = null, Environment $twig = null, EntrypointsJsonLookup $entrypointsJsonLookup = null)
    {
        if (!$container) {
            $container = $this->mockContainer();
        }

        $framework = $this->mockContaoFramework();
        $container->set('contao.framework', $framework);

        if (!$twig) {
            $twig = $this->createMock(Environment::class);
        }

        if (!$entrypointsJsonLookup) {
            $entrypointsJsonLookup = new EntrypointsJsonLookup($container, null);
        }

        if (!$mock) {
            $hookListener = new HookListener($container, $twig, $entrypointsJsonLookup);
        } else {
            $hookListener = $mock->setConstructorArgs([$container, $twig, $entrypointsJsonLookup])->getMock();
        }

        return $hookListener;
    }

    public function testAddEncore()
    {
        $hookListener = $this->getHookListener(null, $this->getMockBuilder(HookListener::class)->setMethods(['doAddEncore']));
        $hookListener->expects($this->once())->method('doAddEncore')->willReturn(true);

        $pageModel = $this->mockClassWithProperties(PageModel::class, []);
        $layoutModel = $this->mockClassWithProperties(LayoutModel::class, []);
        $pageRegular = $this->createMock(PageRegular::class);
        $hookListener->addEncore($pageModel, $layoutModel, $pageRegular);
    }

    public function testDoAddEncore()
    {
        $hookListener = $this->getHookListener();

        $pageModel = $this->mockClassWithProperties(PageModel::class, []);
        $layoutModel = $this->mockClassWithProperties(LayoutModel::class, []);
        $layoutModel->expects($this->once())->method('__get');
        $pageRegular = $this->createMock(PageRegular::class);
        $hookListener->doAddEncore($pageModel, $layoutModel, $pageRegular);

        $layoutModel = $this->mockModelObject(LayoutModel::class, []);
        $layoutModel->expects($this->once())->method('__get');
        $layoutModel->addEncore = '1';
        $hookListener->doAddEncore($pageModel, $layoutModel, $pageRegular);

        $layoutModel = $this->mockModelObject(LayoutModel::class, []);
        $layoutModel->expects($this->once())->method('row');
        $layoutModel->addEncore = '1';
        $container = $this->mockContainer();
        $container->setParameter('huh.encore', '');
        $hookListener = $this->getHookListener($container, $this->getMockBuilder(HookListener::class)->setMethods(['isEntryActive']));
        $hookListener->expects($this->never())->method('isEntryActive');
        $hookListener->doAddEncore($pageModel, $layoutModel, $pageRegular);

        $layoutModel = $this->mockModelObject(LayoutModel::class, []);
        $layoutModel->expects($this->once())->method('row');
        $layoutModel->addEncore = '1';
        $container = $this->mockContainer();
        $container->setParameter('huh.encore', ['encore' => ['entries' => '']]);
        $hookListener = $this->getHookListener($container, $this->getMockBuilder(HookListener::class)->setMethods(['isEntryActive']));
        $hookListener->expects($this->never())->method('isEntryActive');
        $hookListener->doAddEncore($pageModel, $layoutModel, $pageRegular);

        $layoutModel = $this->mockModelObject(LayoutModel::class, []);
        $layoutModel->addEncore = '1';
        $container = $this->mockContainer();
        $container->setParameter('huh.encore', ['encore' => ['entries' => []]]);
        $hookListener = $this->getHookListener($container, $this->getMockBuilder(HookListener::class)->setMethods(['isEntryActive']));
        $hookListener->expects($this->never())->method('isEntryActive');
        $hookListener->doAddEncore($pageModel, $layoutModel, $pageRegular);

        $pageRegular = $this->mockModelObject(PageRegular::class, ['Template' => new \stdClass()]);
        $container = $this->mockContainer();
        $container->setParameter('huh.encore', ['encore' => ['entries' => [
            ['head' => true],
            ['name' => 'contao-utils-bundle'],
            ['name' => 'contao-project-bundle', 'head' => false, 'requiresCss' => true],
            ['name' => 'contao-head-bundle', 'head' => true, 'requiresCss' => false],
        ]]]);
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturnCallback(function ($template, $templateData) {
            switch ($template) {
                case 'default_css':
                    return $templateData['cssEntries'];
                case 'default_js':
                    return $templateData['jsEntries'];
                case 'default_head_js':
                    return $templateData['jsHeadEntries'];
            }
        });
        $hookListener = $this->getHookListener($container, $this->getMockBuilder(HookListener::class)->setMethods(['isEntryActive', 'getItemTemplateByName']), $twig);
        $hookListener->expects($this->exactly(3))->method('isEntryActive')->willReturn(true);
        $hookListener->expects($this->exactly(3))->method('getItemTemplateByName')->willReturnArgument(0);
        $hookListener->doAddEncore($pageModel, $layoutModel, $pageRegular);
        $this->assertCount(1, $pageRegular->Template->encoreStylesheets);
        $this->assertCount(2, $pageRegular->Template->encoreScripts);
        $this->assertCount(1, $pageRegular->Template->encoreHeadScripts);

        $pageRegular = $this->mockModelObject(PageRegular::class, ['Template' => new \stdClass()]);
        $container = $this->mockContainer();
        $container->setParameter('huh.encore', ['encore' => ['entries' => [
            ['name' => 'contao-utils-bundle'],
            ['name' => 'contao-project-bundle', 'head' => false, 'requiresCss' => true],
            ['name' => 'contao-head-bundle', 'head' => true, 'requiresCss' => false],
        ]]]);
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturnCallback(function ($template, $templateData) {
            switch ($template) {
                case 'default_css':
                    return implode(', ', $templateData['cssEntries']);
                case 'default_js':
                    return $templateData['jsEntries'];
                case 'default_head_js':
                    return $templateData['jsHeadEntries'];
            }
        });
        $hookListener = $this->getHookListener($container, $this->getMockBuilder(HookListener::class)->setMethods(['isEntryActive', 'getItemTemplateByName', 'getInlineStylesheets']), $twig);
        $hookListener->expects($this->exactly(3))->method('isEntryActive')->willReturn(true);
        $hookListener->expects($this->exactly(3))->method('getItemTemplateByName')->willReturnArgument(0);
        $hookListener->expects($this->exactly(1))->method('getInlineStylesheets')->willReturnArgument(0);
        $hookListener->doAddEncore($pageModel, $layoutModel, $pageRegular, 'encoreEntries', true);
        $this->assertSame('contao-project-bundle', $pageRegular->Template->encoreStylesheets);
        $this->assertSame('contao-project-bundle', $pageRegular->Template->encoreStylesheetsInline);
        $this->assertCount(2, $pageRegular->Template->encoreScripts);
        $this->assertCount(1, $pageRegular->Template->encoreHeadScripts);
    }

    public function testDoAddEncoreFromEntrypointsJson()
    {
        $pageModel = $this->mockClassWithProperties(PageModel::class, []);

        $layoutModel = $this->mockModelObject(LayoutModel::class, []);
        $layoutModel->addEncore = '1';
        $layoutModel->encoreBabelPolyfillEntryName = 'babel-polyfill';

        $pageRegular = $this->mockModelObject(PageRegular::class, ['Template' => new \stdClass()]);
        $container = $this->mockContainer();
        $container->setParameter('huh.encore', [
            'encore' => [
                'entrypointsJsons' => [
                    __DIR__.'/../entrypoints.json',
                ],
                'entries' => [
                    ['head' => true],
                    ['name' => 'contao-utils-bundle'],
                    ['name' => 'contao-project-bundle', 'head' => false, 'requiresCss' => true],
                    ['name' => 'contao-head-bundle', 'head' => true, 'requiresCss' => false],
                ],
            ], ]);
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturnCallback(function ($template, $templateData) {
            switch ($template) {
                case 'default_css':
                    return $templateData['cssEntries'];
                case 'default_js':
                    return $templateData['jsEntries'];
                case 'default_head_js':
                    return $templateData['jsHeadEntries'];
            }
        });

        $hookListener = $this->getHookListener($container, $this->getMockBuilder(HookListener::class)->setMethods(['isEntryActive', 'getItemTemplateByName']), $twig);
        $hookListener->expects($this->exactly(4))->method('isEntryActive')->willReturn(true);
        $hookListener->expects($this->exactly(3))->method('getItemTemplateByName')->willReturnArgument(0);

        $hookListener->doAddEncore($pageModel, $layoutModel, $pageRegular);

        $this->assertCount(2, $pageRegular->Template->encoreStylesheets);
        $this->assertCount(3, $pageRegular->Template->encoreScripts);
        $this->assertCount(1, $pageRegular->Template->encoreHeadScripts);
    }

    public function testGetInlineStylesheets()
    {
        $webDir = $this->getTempDir().'/web';
        $container = $this->mockContainer($this->getTempDir());
        $container->setParameter('contao.web_dir', $webDir);
        $hookListener = $this->getHookListener($container);

        $this->assertFalse($hookListener->getInlineStylesheets(''));

        $filesystem = new Filesystem();
        $filesystem->dumpFile($webDir.'/styles.css', '.style{}');
        $this->assertSame('.style{}', $hookListener->getInlineStylesheets('<link rel="stylesheet" href="/styles.css">'));
    }
}
