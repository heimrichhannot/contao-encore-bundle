<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\EventListener\Contao;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Asset\GlobalContaoAsset;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPoint;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointBuilderFactory;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPoints;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointsBuilder;
use HeimrichHannot\EncoreBundle\EventListener\Contao\ReplaceDynamicScriptTagsListener;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use HeimrichHannot\TestUtilitiesBundle\Mock\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Util\RequestUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;

class ReplaceDynamicScriptTagsListenerTest extends ContaoTestCase
{
    use ModelMockTrait;

    public function createTestInstance(array $parameter = []): ReplaceDynamicScriptTagsListener
    {
        $parameter['utils'] = $parameter['utils'] ?? $this->createMock(Utils::class);
        $parameter['configurationHelper'] = $parameter['configurationHelper'] ?? $this->createMock(ConfigurationHelper::class);
        $parameter['globalContaoAsset'] = $parameter['globalContaoAsset'] ?? $this->createMock(GlobalContaoAsset::class);
        $parameter['entryPointBuilderFactory'] = $parameter['entryPointBuilderFactory'] ?? $this->createMock(EntryPointBuilderFactory::class);
        $parameter['frontendAsset'] = $parameter['frontendAsset'] ?? $this->createMock(FrontendAsset::class);
        $parameter['tagRenderer'] = $parameter['tagRenderer'] ?? $this->createMock(TagRenderer::class);
        $parameter['requestStack'] = $parameter['requestStack'] ?? $this->createMock(RequestStack::class);

        return new ReplaceDynamicScriptTagsListener(
            $parameter['utils'],
            $parameter['configurationHelper'],
            $parameter['globalContaoAsset'],
            entryPointBuilderFactory: $parameter['entryPointBuilderFactory'],
            frontendAsset: $parameter['frontendAsset'],
            tagRenderer: $parameter['tagRenderer'],
            requestStack: $parameter['requestStack']
        );
    }

    public function testInvoke()
    {
        $requestUtil = $this->createMock(RequestUtil::class);
        $requestUtil->method('getCurrentPageModel')->willReturn(null);

        $utils = $this->createMock(Utils::class);
        $utils->method('request')->willReturn($requestUtil);

        $configurationHelper = $this->createMock(ConfigurationHelper::class);
        $configurationHelper->expects($this->never())->method('isEnabledOnPage');

        $entryPointBuilderFactory = $this->createMock(EntryPointBuilderFactory::class);
        $entryPointBuilderFactory->expects($this->never())->method('create');

        $globalContaoAsset = $this->createMock(GlobalContaoAsset::class);
        $globalContaoAsset->expects($this->never())->method('cleanGlobalArrayFromConfiguration');

        $instance = $this->createTestInstance([
            'utils' => $utils,
            'configurationHelper' => $configurationHelper,
            'globalContaoAsset' => $globalContaoAsset,
            'entryPointBuilderFactory' => $entryPointBuilderFactory,
        ]);

        $this->assertSame('test', $instance->__invoke('test'));

        $pageModel = $this->mockModelObject(PageModel::class, [
            'id' => 1,
        ]);

        $requestUtil = $this->createMock(RequestUtil::class);
        $requestUtil->method('getCurrentPageModel')->willReturn($pageModel);

        $utils = $this->createMock(Utils::class);
        $utils->method('request')->willReturn($requestUtil);

        $configurationHelper = $this->createMock(ConfigurationHelper::class);
        $configurationHelper->expects($this->once())
            ->method('isEnabledOnPage')
            ->with($pageModel)
            ->willReturn(false);

        $entryPointBuilderFactory = $this->createMock(EntryPointBuilderFactory::class);
        $entryPointBuilderFactory->expects($this->never())->method('create');

        $globalContaoAsset = $this->createMock(GlobalContaoAsset::class);
        $globalContaoAsset->expects($this->never())->method('cleanGlobalArrayFromConfiguration');

        $instance = $this->createTestInstance([
            'utils' => $utils,
            'configurationHelper' => $configurationHelper,
            'globalContaoAsset' => $globalContaoAsset,
            'entryPointBuilderFactory' => $entryPointBuilderFactory,
        ]);

        $this->assertSame('test', $instance->__invoke('test'));

        $entryPoints = new EntryPoints();
        $entryPoints->add(new EntryPoint('app', head: true, requiresCss: true));
        $entryPoints->add(new EntryPoint('deferred', head: false, requiresCss: false));
        $entryPoints->add(new EntryPoint('inactive', active: false, head: true, requiresCss: true));

        $builder = $this->createMock(EntryPointsBuilder::class);
        $builder->expects($this->once())->method('setFrontendAsset')->with($this->isInstanceOf(FrontendAsset::class))->willReturnSelf();
        $builder->expects($this->once())->method('setPage')->with($pageModel)->willReturnSelf();
        $builder->expects($this->once())->method('build')->willReturn($entryPoints);

        $entryPointBuilderFactory = $this->createMock(EntryPointBuilderFactory::class);
        $entryPointBuilderFactory->expects($this->once())->method('create')->willReturn($builder);

        $configurationHelper = $this->createMock(ConfigurationHelper::class);
        $configurationHelper->expects($this->once())
            ->method('isEnabledOnPage')
            ->with($pageModel)
            ->willReturn(true);

        $globalContaoAsset = $this->createMock(GlobalContaoAsset::class);
        $globalContaoAsset->expects($this->once())->method('cleanGlobalArrayFromConfiguration');

        $tagRenderer = $this->createMock(TagRenderer::class);
        $tagRenderer->expects($this->once())
            ->method('renderWebpackLinkTags')
            ->with('app')
            ->willReturn('<link-app>');
        $tagRenderer->expects($this->exactly(2))
            ->method('renderWebpackScriptTags')
            ->willReturnCallback(static fn (string $entryName): string => match ($entryName) {
                'app' => '<script-app>',
                'deferred' => '<script-deferred>',
                default => throw new \InvalidArgumentException(sprintf('Unexpected entry "%s".', $entryName)),
            });

        $request = new Request();
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $instance = $this->createTestInstance([
            'utils' => $utils,
            'configurationHelper' => $configurationHelper,
            'globalContaoAsset' => $globalContaoAsset,
            'entryPointBuilderFactory' => $entryPointBuilderFactory,
            'tagRenderer' => $tagRenderer,
            'requestStack' => $requestStack,
        ]);

        $nonce = '_' . ContaoFramework::getNonce();
        $buffer = "[[TL_CSS$nonce]] [[TL_HEAD$nonce]] [[TL_BODY$nonce]]";
        $expected = "[[TL_CSS$nonce]]<link-app> <script-app>[[TL_HEAD$nonce]] <script-deferred>[[TL_BODY$nonce]]";

        $this->assertSame($expected, $instance->__invoke($buffer));
        $this->assertSame($entryPoints, $request->attributes->get('encore_entries'));
    }
}
