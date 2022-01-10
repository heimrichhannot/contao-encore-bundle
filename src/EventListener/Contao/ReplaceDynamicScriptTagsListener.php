<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\LayoutModel;
use Contao\PageModel;
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use HeimrichHannot\EncoreBundle\Helper\EntryHelper;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

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
     * @var ModelUtil
     */
    protected $modelUtil;
    /**
     * @var TemplateAsset
     */
    protected $templateAsset;
    /**
     * @var ConfigurationHelper
     */
    protected $configurationHelper;

    /**
     * ReplaceDynamicScriptTagsListener constructor.
     */
    public function __construct(array $bundleConfig, ModelUtil $modelUtil, TemplateAsset $templateAsset, ConfigurationHelper $configurationHelper)
    {
        $this->bundleConfig = $bundleConfig;
        $this->modelUtil = $modelUtil;
        $this->templateAsset = $templateAsset;
        $this->configurationHelper = $configurationHelper;
    }

    public function __invoke(string $buffer): string
    {
        if (!$this->configurationHelper->isEnabledOnCurrentPage()) {
            return $buffer;
        }

        global $objPage;
        $layout = $this->modelUtil->findModelInstanceByPk('tl_layout', $objPage->layoutId);

        if (!$layout) {
            return $buffer;
        }

        if (!isset($this->bundleConfig['use_contao_template_variables']) || true !== $this->bundleConfig['use_contao_template_variables']) {
            $buffer = $this->replaceEncoreTags($buffer, $objPage, $layout);
        } else {
            $buffer = $this->replaceContaoTags($buffer, $objPage, $layout);
        }

        $this->cleanGlobalArrays();

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

        $replace = [];
        $replace['[[TL_CSS]]'] = '[[TL_CSS]]'.trim($templateAssets->linkTags());

        // caution: always render head first because of global dependencies like jQuery
        $replace['[[TL_HEAD]]'] = trim($templateAssets->headScriptTags()).'[[TL_HEAD]]';
        $replace['[[TL_BODY]]'] = trim($templateAssets->scriptTags()).'[[TL_BODY]]';

        return str_replace(array_keys($replace), $replace, $buffer);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function cleanGlobalArrays()
    {
        EntryHelper::cleanGlobalArrays($this->bundleConfig);
    }
}
