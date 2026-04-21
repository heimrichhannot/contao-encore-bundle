<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\EventListener\Contao;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContext;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
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

    private function createResponseContextAccessor(?ResponseContext $responseContext = null): ResponseContextAccessor
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        $accessor = new ResponseContextAccessor($requestStack);

        if (null !== $responseContext) {
            $accessor->setResponseContext($responseContext);
        }

        return $accessor;
    }

    public function createTestInstance(array $parameter = []): ReplaceDynamicScriptTagsListener
    {
        $parameter['utils'] = $parameter['utils'] ?? $this->createMock(Utils::class);
        $parameter['configurationHelper'] = $parameter['configurationHelper'] ?? $this->createMock(ConfigurationHelper::class);
        $parameter['globalContaoAsset'] = $parameter['globalContaoAsset'] ?? $this->createMock(GlobalContaoAsset::class);
        $parameter['entryPointBuilderFactory'] = $parameter['entryPointBuilderFactory'] ?? $this->createMock(EntryPointBuilderFactory::class);
        $parameter['tagRenderer'] = $parameter['tagRenderer'] ?? $this->createMock(TagRenderer::class);
        $parameter['requestStack'] = $parameter['requestStack'] ?? $this->createMock(RequestStack::class);
        $parameter['responseContextAccessor'] = $parameter['responseContextAccessor'] ?? $this->createResponseContextAccessor();

        return new ReplaceDynamicScriptTagsListener(
            $parameter['utils'],
            $parameter['configurationHelper'],
            $parameter['globalContaoAsset'],
            $parameter['entryPointBuilderFactory'],
            $parameter['tagRenderer'],
            $parameter['requestStack'],
            $parameter['responseContextAccessor'],
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

        $responseContext = new ResponseContext();

        $builder = $this->createMock(EntryPointsBuilder::class);
        $builder->expects($this->once())->method('setResponseContext')->with($responseContext)->willReturnSelf();
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
            'responseContextAccessor' => $this->createResponseContextAccessor($responseContext),
        ]);

        $nonce = '_' . ContaoFramework::getNonce();
        $buffer = "[[TL_CSS$nonce]] [[TL_HEAD$nonce]] [[TL_BODY$nonce]]";
        $expected = "[[TL_CSS$nonce]]<link-app> <script-app>[[TL_HEAD$nonce]] <script-deferred>[[TL_BODY$nonce]]";

        $this->assertSame($expected, $instance->__invoke($buffer));
        $this->assertSame($entryPoints, $request->attributes->get('encore_entries'));
    }
}
