<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener\Contao;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\LayoutModel;
use Contao\PageModel;
use HeimrichHannot\EncoreBundle\Asset\GlobalContaoAsset;
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use HeimrichHannot\EncoreBundle\Helper\EntryHelper;
use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * @Hook("replaceDynamicScriptTags")
 */
class ReplaceDynamicScriptTagsListener
{
    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var TemplateAsset
     */
    protected $templateAsset;
    /**
     * @var ConfigurationHelper
     */
    protected $configurationHelper;
    private GlobalContaoAsset $globalContaoAsset;
    private ContaoFramework   $contaoFramework;
    private Utils             $utils;

    /**
     * ReplaceDynamicScriptTagsListener constructor.
     */
    public function __construct(array $bundleConfig, ContaoFramework $contaoFramework, Utils $utils, TemplateAsset $templateAsset, ConfigurationHelper $configurationHelper, GlobalContaoAsset $globalContaoAsset)
    {
        $this->bundleConfig = $bundleConfig;
        $this->templateAsset = $templateAsset;
        $this->configurationHelper = $configurationHelper;
        $this->globalContaoAsset = $globalContaoAsset;
        $this->contaoFramework = $contaoFramework;
        $this->utils = $utils;
    }

    public function __invoke(string $buffer): string
    {
        if (!$this->configurationHelper->isEnabledOnCurrentPage()) {
            return $buffer;
        }

        $pageModel = $this->utils->request()->getCurrentPageModel();

        if (!$pageModel) {
            return $buffer;
        }

        $pageModel->loadDetails();

        if (!($layout = $this->contaoFramework->getAdapter(LayoutModel::class)->findByPk(($pageModel->layoutId ?? $pageModel->layout)))) {
            return $buffer;
        }
        /* @var LayoutModel|null $layout */

        if (!isset($this->bundleConfig['use_contao_template_variables']) || true !== $this->bundleConfig['use_contao_template_variables']) {
            $buffer = $this->replaceEncoreTags($buffer, $pageModel, $layout);
        } else {
            $buffer = $this->replaceContaoTags($buffer, $pageModel, $layout);
        }

        $this->globalContaoAsset->cleanGlobalArrayFromConfiguration();

        return $buffer;
    }

    protected function replaceEncoreTags(string $buffer, PageModel $page, LayoutModel $layout): string
    {
        $templateAssets = $this->templateAsset->createInstance($page, $layout, 'encoreEntries');

        $replace = [];
        $replace['[[HUH_ENCORE_CSS]]'] = trim($templateAssets->linkTags());
        // caution: always render head first because of global dependencies like jQuery
        $replace['[[HUH_ENCORE_HEAD_JS]]'] = trim($templateAssets->headScriptTags());
        $replace['[[HUH_ENCORE_JS]]'] = trim($templateAssets->scriptTags());

        return str_replace(array_keys($replace), $replace, $buffer);
    }

    protected function replaceContaoTags(string $buffer, PageModel $page, LayoutModel $layout): string
    {
        $templateAssets = $this->templateAsset->createInstance($page, $layout, 'encoreEntries');

        $nonce = '';
        if (method_exists(ContaoFramework::class, 'getNonce')) {
            $nonce = '_'.ContaoFramework::getNonce();
        }

        $replace = [];
        $replace["[[TL_CSS$nonce]]"] = "[[TL_CSS$nonce]]".trim($templateAssets->linkTags());

        // caution: always render head first because of global dependencies like jQuery
        $replace["[[TL_HEAD$nonce]]"] = trim($templateAssets->headScriptTags())."[[TL_HEAD$nonce]]";
        $replace["[[TL_BODY$nonce]]"] = trim($templateAssets->scriptTags())."[[TL_BODY$nonce]]";

        return str_replace(array_keys($replace), $replace, $buffer);
    }

    /**
     * @codeCoverageIgnore
     *
     * @deprecated
     */
    protected function cleanGlobalArrays()
    {
        EntryHelper::cleanGlobalArrays($this->bundleConfig);
    }
}
