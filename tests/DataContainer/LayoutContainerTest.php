<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\DataContainer;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\DataContainer;
use Contao\LayoutModel;
use Contao\Message;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\DataContainer\LayoutContainer;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class LayoutContainerTest extends ContaoTestCase
{
    public function createTestInstance(array $parameters = []): LayoutContainer
    {
        $bundleConfig = $parameters['bundleConfig'] ?? [];
        $contaoFramework = $parameters['contaoFramework'] ?? $this->mockContaoFramework();
        $requestStack = $parameters['requestStack'] ?? $this->createMock(RequestStack::class);
        $scopeMatcher = $parameters['scopeMatcher'] ?? $this->createMock(ScopeMatcher::class);
        $entryCollection = $parameters['entryCollection'] ?? $this->createMock(EntryCollection::class);
        $translator = $parameters['translator'] ?? $this->createMock(TranslatorInterface::class);

        return new LayoutContainer(
            $bundleConfig,
            $contaoFramework,
            $requestStack,
            $scopeMatcher,
            $entryCollection,
            $translator
        );
    }

    public function testOnLoadCallback()
    {
        $GLOBALS['TL_LANG']['tl_layout']['INFO']['jquery_order_conflict'] = 'Info';

        $messageAdapter = $this->mockAdapter(['addError', 'addInfo', 'generateUnwrapped', 'hasMessages']);
        $messageAdapter->expects($this->never())->method('addInfo')->willReturn(null);

        /** @var ContaoFramework|MockObject $contaoFramework */
        $contaoFramework = $this->mockContaoFramework([
            Message::class => $messageAdapter,
        ]);

        $instance = $this->createTestInstance([
            'contaoFramework' => $contaoFramework,
        ]);
        $instance->onLoadCallback(null);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(new Request());

        $instance = $this->createTestInstance([
            'contaoFramework' => $contaoFramework,
            'requestStack' => $requestStack,
        ]);
        $instance->onLoadCallback(null);

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $instance->onLoadCallback($dc);

        $instance = $this->createTestInstance([
            'contaoFramework' => $contaoFramework,
            'requestStack' => $requestStack,
        ]);
        $instance->onLoadCallback($dc);

        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher->method('isBackendRequest')->willReturn(true);

        $layoutModel = $this->mockAdapter(['findByPk']);
        $layoutModel->method('findByPk')->willReturn($this->mockClassWithProperties(LayoutModel::class, [
            'addEncore' => true,
            'addJQuery' => true,
        ]));

        $messageAdapter = $this->mockAdapter(['addError', 'addInfo', 'generateUnwrapped', 'hasMessages']);
        $messageAdapter->expects($this->once())->method('addInfo')->willReturn(null);
        /** @var ContaoFramework|MockObject $contaoFramework */
        $contaoFramework = $this->mockContaoFramework([
            Message::class => $messageAdapter,
            LayoutModel::class => $layoutModel,
        ]);
        $instance = $this->createTestInstance([
            'contaoFramework' => $contaoFramework,
            'scopeMatcher' => $scopeMatcher,
            'requestStack' => $requestStack,
        ]);
        $instance->onLoadCallback($dc);

        $bundleConfig['unset_jquery'] = true;
        $messageAdapter = $this->mockAdapter(['addError', 'addInfo', 'generateUnwrapped', 'hasMessages']);
        $messageAdapter->expects($this->never())->method('addInfo')->willReturn(null);
        /** @var ContaoFramework|MockObject $contaoFramework */
        $contaoFramework = $this->mockContaoFramework([
            Message::class => $messageAdapter,
            LayoutModel::class => $layoutModel,
        ]);
        $instance = $this->createTestInstance([
            'contaoFramework' => $contaoFramework,
            'bundleConfig' => $bundleConfig,
            'scopeMatcher' => $scopeMatcher,
            'requestStack' => $requestStack,
        ]);
        $instance->onLoadCallback($dc);
    }

    public function testOnImportTemplateOptionsCallback()
    {
        $instance = $this->createTestInstance();
        $this->assertEmpty($instance->onImportTemplateOptionsCallback());

        $instance = $this->createTestInstance(['bundleConfig' => [
            'templates' => ['imports' => []],
        ]]);
        $this->assertEmpty($instance->onImportTemplateOptionsCallback());

        $instance = $this->createTestInstance(['bundleConfig' => [
            'templates' => ['imports' => [
                ['name' => 'js_default', 'template' => '@Encore/js_default.html.twig'],
                ['name' => 'css_default', 'template' => '@Encore/css_default.html.twig'],
            ]],
        ]]);
        $this->assertSame([
            'css_default' => '@Encore/css_default.html.twig',
            'js_default' => '@Encore/js_default.html.twig',
        ], $instance->onImportTemplateOptionsCallback());
    }
}
