<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\EventListener;

use Contao\LayoutModel;
use Contao\Model;
use Contao\PageModel;
use Contao\PageRegular;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\EntrypointsJsonLookup;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Asset\PageEntrypoints;
use HeimrichHannot\EncoreBundle\EventListener\HookListener;
use HeimrichHannot\EncoreBundle\Test\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class HookListenerTest extends ContaoTestCase
{
    use ModelMockTrait;

    /**
     * @param array $parameters
     * @param null $hookListenerMock
     * @return HookListener
     */
    public function createHookListener(array $parameters = [], $hookListenerMock = null)
    {
        if (!isset($parameters['container'])) {
            $parameters['container'] = $this->mockContainer();
        }
        if (!isset($parameters['framework'])) {
            $parameters['container']->set('contao.framework', $this->mockContaoFramework());
        }
        if (!isset($parameters['twig'])) {
            $twig = $this->createMock(Environment::class);
            $twig->method('render')->willReturnArgument(1);
            $parameters['twig'] = $twig;
        }
        if (!isset($parameters['pageEntrypoints'])) {
            $pageEntrypoints = $this->createMock(PageEntrypoints::class);
            $pageEntrypoints->method('generatePageEntrypoints')->willReturn(true);
            $parameters['pageEntrypoints'] = $pageEntrypoints;
        }

        if (isset($parameters['bundleConfig'])) {
            $parameters['container']->setParameter('huh_encore', $parameters['bundleConfig']);
        }

        if (!$hookListenerMock) {
            $hookListener = new HookListener($parameters['container'], $parameters['twig'], $parameters['pageEntrypoints']);
        } else {
            $hookListener = $hookListenerMock->setConstructorArgs([$parameters['container'], $parameters['twig'], $parameters['pageEntrypoints']])->getMock();
        }

        return $hookListener;
    }

    public function testOnGeneratePage()
    {
        $hookListener = $this->createHookListener([], $this->getMockBuilder(HookListener::class)->setMethods(['addEncore', 'cleanGlobalArrays']));
        $hookListener->expects($this->once())->method('addEncore')->willReturn(true);
        $hookListener->expects($this->once())->method('cleanGlobalArrays')->willReturn(true);

        $pageModel = $this->mockClassWithProperties(PageModel::class, []);
        $layoutModel = $this->mockClassWithProperties(LayoutModel::class, []);
        $pageRegular = $this->createMock(PageRegular::class);
        $hookListener->onGeneratePage($pageModel, $layoutModel, $pageRegular);
    }

    public function testGetInlineStylesheets()
    {
        $webDir = $this->getTempDir().'/web';
        $container = $this->mockContainer($this->getTempDir());
        $container->setParameter('contao.web_dir', $webDir);
        $hookListener = $this->createHookListener(['container' => $container]);

        $this->assertFalse($hookListener->getInlineStylesheets(''));

        $filesystem = new Filesystem();
        $filesystem->dumpFile($webDir.'/styles.css', '.style{}');
        $this->assertSame('.style{}', $hookListener->getInlineStylesheets('<link rel="stylesheet" href="/styles.css">'));
    }
    
    public function testGetItemTemplateByName()
    {

        $hookListener = $this->createHookListener(['bundleConfig' => ['templates' => []]]);
        $this->assertNull($hookListener->getItemTemplateByName('default_css'));

        $hookListener = $this->createHookListener(['bundleConfig' => ['templates' => ['imports' => [['name' => 'default_css', 'template' => 'encore_css_imports.html.twig']]]]]);
        $this->assertSame('encore_css_imports.html.twig', $hookListener->getItemTemplateByName('default_css'));
        $this->assertNull($hookListener->getItemTemplateByName('another_css'));
    }
}
