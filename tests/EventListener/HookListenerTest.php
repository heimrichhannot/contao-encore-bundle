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
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use HeimrichHannot\EncoreBundle\EventListener\HookListener;
use HeimrichHannot\EncoreBundle\Test\ModelMockTrait;
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
        if (!isset($parameters['templateAsset'])){
            $templateAsset = $this->createMock(TemplateAsset::class);
            $templateAsset->method('createInstance')->willReturnSelf();
            $templateAsset->method('linkTags')->willReturn('');
            $templateAsset->method('inlineCssLinkTag')->willReturn('');
            $templateAsset->method('scriptTags')->willReturn('');
            $templateAsset->method('headScriptTags')->willReturn('');
            $parameters['templateAsset'] = $templateAsset;
        }

        if (isset($parameters['bundleConfig'])) {
            $parameters['container']->setParameter('huh_encore', $parameters['bundleConfig']);
        }

        if (!$hookListenerMock) {
            $hookListener = new HookListener($parameters['container'], $parameters['twig'], $parameters['pageEntrypoints']);
        } else {
            $hookListener = $hookListenerMock->setConstructorArgs([$parameters['container'], $parameters['twig'], $parameters['templateAsset']])->getMock();
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

}
