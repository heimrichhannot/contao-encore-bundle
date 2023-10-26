<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\EventListener\Contao;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Page;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\GlobalContaoAsset;
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use HeimrichHannot\EncoreBundle\EventListener\Contao\ReplaceDynamicScriptTagsListener;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use HeimrichHannot\TestUtilitiesBundle\Mock\ModelMockTrait;
use HeimrichHannot\UtilsBundle\Util\Request\RequestUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;

class ReplaceDynamicScriptTagsListenerTest extends ContaoTestCase
{
    use ModelMockTrait;

    /**
     * @return ReplaceDynamicScriptTagsListener|MockObject
     */
    public function createTestInstance(array $parameter = [], ?MockBuilder $mockBuilder = null)
    {
        $parameter['bundleConfig'] = $parameter['bundleConfig'] ?? [];
        $parameter['contaoFramework'] = $parameter['contaoFramework'] ?? $this->mockContaoFramework();
        $parameter['utils'] = $parameter['utils'] ?? $this->createMock(Utils::class);
        $parameter['templateAsset'] = $parameter['templateAsset'] ?? $this->createMock(TemplateAsset::class);
        $parameter['configurationHelper'] = $parameter['configurationHelper'] ?? $this->createMock(ConfigurationHelper::class);
        $parameter['globalContaoAsset'] = $parameter['globalContaoAsset'] ?? $this->createMock(GlobalContaoAsset::class);

        if ($mockBuilder) {
            $instance = $mockBuilder->setConstructorArgs([
                $parameter['bundleConfig'],
                $parameter['contaoFramework'],
                $parameter['utils'],
                $parameter['templateAsset'],
                $parameter['configurationHelper'],
                $parameter['globalContaoAsset'],
            ])->getMock();
        } else {
            $instance = new ReplaceDynamicScriptTagsListener(
                $parameter['bundleConfig'],
                $parameter['contaoFramework'],
                $parameter['utils'],
                $parameter['templateAsset'],
                $parameter['configurationHelper'],
                $parameter['globalContaoAsset'],
            );
        }

        return $instance;
    }

    public function testInvoke()
    {
        //
        // Encore not enabled
        //

        $configurationHelper = $this->createMock(ConfigurationHelper::class);
        $configurationHelper->method('isEnabledOnCurrentPage')->willReturn(false);

        $utils = $this->createMock(Utils::class);
        $utils->expects($this->never())->method('request');

        $instance = $this->createTestInstance([
            'utils' => $utils,
            'configurationHelper' => $configurationHelper,
        ]);
        $instance->__invoke('test');

        //
        // No page
        //

        $configurationHelper = $this->createMock(ConfigurationHelper::class);
        $configurationHelper->method('isEnabledOnCurrentPage')->willReturn(true);

        $requestUtil = $this->createMock(RequestUtil::class);
        $requestUtil->method('getCurrentPageModel')->willReturn(null);
        $utils = $this->createMock(Utils::class);
        $utils->method('request')->willReturn($requestUtil);

        $layoutAdapter = $this->mockAdapter(['findByPk']);
        $layoutAdapter->expects($this->never())->method('findByPk');

        $framework = $this->mockContaoFramework([
            LayoutModel::class => $layoutAdapter,
        ]);

        $instance = $this->createTestInstance([
            'utils' => $utils,
            'configurationHelper' => $configurationHelper,
            'contaoFramework' => $framework,
        ]);

        $instance->__invoke('test');

        //
        // No Layout
        //

        $requestUtil = $this->createMock(RequestUtil::class);
        $requestUtil->method('getCurrentPageModel')->willReturn($this->mockModelObject(PageModel::class, [
            'layoutId' => 3,
        ]));
        $utils = $this->createMock(Utils::class);
        $utils->method('request')->willReturn($requestUtil);

        $layoutAdapter = $this->mockAdapter(['findByPk']);
        $layoutAdapter->method('findByPk')->willReturn(null);

        $framework = $this->mockContaoFramework([
            LayoutModel::class => $layoutAdapter,
        ]);

        $templateAssetMock = $this->createMock(TemplateAsset::class);
        $templateAssetMock->method('createInstance')->willReturnSelf();
        $templateAssetMock->method('linkTags')->willReturn('<link>');
        $templateAssetMock->method('scriptTags')->willReturn('<script>');
        $templateAssetMock->method('headScriptTags')->willReturn('<head>');

        $instance = $this->createTestInstance([
            'utils' => $utils,
            'configurationHelper' => $configurationHelper,
            'contaoFramework' => $framework,
            'templateAsset' => $templateAssetMock,
        ]);

        $this->assertSame('[[HUH_ENCORE_CSS]]', $instance->__invoke('[[HUH_ENCORE_CSS]]'));

        //
        // With Layout
        //

        $layoutAdapter = $this->mockAdapter(['findByPk']);
        $layoutAdapter->method('findByPk')->willReturn($this->mockModelObject(LayoutModel::class, []));

        $framework = $this->mockContaoFramework([
            LayoutModel::class => $layoutAdapter,
        ]);

        $instance = $this->createTestInstance([
            'utils' => $utils,
            'configurationHelper' => $configurationHelper,
            'contaoFramework' => $framework,
            'templateAsset' => $templateAssetMock,
        ]);

        $nonce = '';
        if (method_exists(ContaoFramework::class, 'getNonce')) {
            $nonce = '_'.ContaoFramework::getNonce();
        }

        $this->assertSame('[[HUH_ENCORE_CSS]]', $instance->__invoke('[[HUH_ENCORE_CSS]]'));
        $this->assertSame("[[TL_CSS$nonce]]<link>", $instance->__invoke("[[TL_CSS$nonce]]"));
    }
}
