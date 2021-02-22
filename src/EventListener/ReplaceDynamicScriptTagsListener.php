<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener;

use Contao\LayoutModel;
use Contao\PageModel;
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use HeimrichHannot\EncoreBundle\Helper\EntryHelper;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\HttpFoundation\RequestStack;

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
     * @var ContainerUtil
     */
    protected $containerUtil;
    /**
     * @var ModelUtil
     */
    protected $modelUtil;
    /**
     * @var TemplateAsset
     */
    protected $templateAsset;
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * ReplaceDynamicScriptTagsListener constructor.
     */
    public function __construct(array $bundleConfig, ContainerUtil $containerUtil, ModelUtil $modelUtil, TemplateAsset $templateAsset, RequestStack $requestStack)
    {
        $this->bundleConfig = $bundleConfig;
        $this->containerUtil = $containerUtil;
        $this->modelUtil = $modelUtil;
        $this->templateAsset = $templateAsset;
        $this->requestStack = $requestStack;
    }

    public function __invoke(string $buffer): string
    {
        if (!$this->containerUtil->isFrontend()) {
            return $buffer;
        }

        if (null !== $this->requestStack->getParentRequest()) {
            return $buffer;
        }

        global $objPage;
        $layout = $this->modelUtil->findModelInstanceByPk('tl_layout', $objPage->layoutId);
        if (!$layout || !$layout->addEncore) {
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
        $replace['[[HUH_ENCORE_JS]]'] = trim($templateAssets->scriptTags());
        $replace['[[HUH_ENCORE_HEAD_JS]]'] = trim($templateAssets->headScriptTags());

        return str_replace(array_keys($replace), $replace, $buffer);
    }

    protected function replaceContaoTags(string $buffer, PageModel $page, LayoutModel $layout): string
    {
        $templateAssets = $this->templateAsset->createInstance($page, $layout, 'encoreEntries');

        $replace = [];
        $replace['[[TL_CSS]]'] = '[[TL_CSS]]'.trim($templateAssets->linkTags());
        $replace['[[TL_BODY]]'] = trim($templateAssets->scriptTags()).'[[TL_BODY]]';
        $replace['[[TL_HEAD]]'] = '[[TL_HEAD]]'.trim($templateAssets->headScriptTags());

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
