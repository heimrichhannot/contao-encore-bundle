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
use Contao\ThemeModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\DataContainer\LayoutContainer;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
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
            $translator,
            new ConfigurationHelper($requestStack, new ParameterBag(), $scopeMatcher, $contaoFramework, $this->createMock(EventDispatcherInterface::class))
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

    /** @dataProvider inheritedActivationProvider */
    public function testThemeActivationControlsLayoutWarnings(string $mode, string $themeEnabled, bool $expected): void
    {
        $GLOBALS['TL_LANG']['tl_layout']['INFO']['jquery_order_conflict'] = 'Info';
        $theme = $this->mockClassWithProperties(ThemeModel::class, ['addEncore' => $themeEnabled]);
        $layout = $this->mockClassWithProperties(LayoutModel::class, ['addEncore' => $mode, 'addJQuery' => '1']);
        $layout->method('getRelated')->with('pid')->willReturn($theme);
        $layoutAdapter = $this->mockAdapter(['findByPk']);
        $layoutAdapter->method('findByPk')->willReturn($layout);
        $messages = $this->mockAdapter(['addError', 'addInfo', 'hasMessages']);
        $messages->expects($expected ? $this->once() : $this->never())->method('addInfo')->with('Info');
        $framework = $this->mockContaoFramework([LayoutModel::class => $layoutAdapter, Message::class => $messages]);
        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $scope = $this->createMock(ScopeMatcher::class);
        $scope->method('isBackendRequest')->willReturn(true);
        $this->createTestInstance(['requestStack' => $requestStack, 'scopeMatcher' => $scope, 'contaoFramework' => $framework])
            ->onLoadCallback($this->mockClassWithProperties(DataContainer::class, ['id' => 1]));
    }

    public function inheritedActivationProvider(): array
    {
        return [['i', '1', true], ['i', '', false], ['0', '1', false], ['1', '', true]];
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
