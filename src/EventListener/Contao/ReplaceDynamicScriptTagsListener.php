<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\LayoutModel;
use Contao\PageModel;
use HeimrichHannot\EncoreBundle\Asset\GlobalContaoAsset;
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use HeimrichHannot\UtilsBundle\Util\Utils;

#[AsHook('replaceDynamicScriptTags')]
class ReplaceDynamicScriptTagsListener
{
    public function __construct(
        protected array $bundleConfig,
        private readonly ContaoFramework $contaoFramework,
        private readonly Utils $utils,
        protected TemplateAsset $templateAsset,
        protected ConfigurationHelper $configurationHelper,
        private readonly GlobalContaoAsset $globalContaoAsset,
    ) {
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

        if (!($layout = $this->contaoFramework->getAdapter(LayoutModel::class)->findByPk($pageModel->layoutId ?? $pageModel->layout))) {
            return $buffer;
        }
        /* @var LayoutModel|null $layout */
        $buffer = $this->replaceContaoTags($buffer, $pageModel, $layout);
        $this->globalContaoAsset->cleanGlobalArrayFromConfiguration();

        return $buffer;
    }

    protected function replaceContaoTags(string $buffer, PageModel $page, LayoutModel $layout): string
    {
        $templateAssets = $this->templateAsset->createInstance($page, $layout, 'encoreEntries');

        $nonce = '';
        if (method_exists(ContaoFramework::class, 'getNonce')) {
            $nonce = '_' . ContaoFramework::getNonce();
        }

        $replace = [];
        $replace["[[TL_CSS$nonce]]"] = "[[TL_CSS$nonce]]" . trim($templateAssets->linkTags());

        // caution: always render head first because of global dependencies like jQuery
        $replace["[[TL_HEAD$nonce]]"] = trim($templateAssets->headScriptTags()) . "[[TL_HEAD$nonce]]";
        $replace["[[TL_BODY$nonce]]"] = trim($templateAssets->scriptTags()) . "[[TL_BODY$nonce]]";

        return str_replace(array_keys($replace), $replace, $buffer);
    }
}
