<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use HeimrichHannot\EncoreBundle\Asset\GlobalContaoAsset;
use HeimrichHannot\EncoreBundle\EntryPoint\EntryPointBuilderFactory;
use HeimrichHannot\EncoreBundle\Helper\ConfigurationHelper;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;

#[AsHook('replaceDynamicScriptTags')]
class ReplaceDynamicScriptTagsListener
{
    public function __construct(
        private readonly Utils $utils,
        protected ConfigurationHelper $configurationHelper,
        private readonly GlobalContaoAsset $globalContaoAsset,
        private readonly EntryPointBuilderFactory $entryPointBuilderFactory,
        private readonly TagRenderer $tagRenderer,
        private readonly RequestStack $requestStack,
        private readonly ResponseContextAccessor $responseContextAccessor,
    ) {
    }

    public function __invoke(string $buffer): string
    {
        $pageModel = $this->utils->request()->getCurrentPageModel();

        if (!$pageModel) {
            return $buffer;
        }

        if (!$this->configurationHelper->isEnabledOnPage($pageModel)) {
            return $buffer;
        }

        $nonce = '_' . ContaoFramework::getNonce();
        $hasCssPlaceholder = str_contains($buffer, "[[TL_CSS$nonce]]");
        $hasHeadPlaceholder = str_contains($buffer, "[[TL_HEAD$nonce]]");
        $hasBodyPlaceholder = str_contains($buffer, "[[TL_BODY$nonce]]");

        // Legacy fragments also trigger this hook. Rendering tags without a matching
        // placeholder would consume Encore's assets before the page can output them.
        if (!$hasCssPlaceholder && !$hasHeadPlaceholder && !$hasBodyPlaceholder) {
            return $buffer;
        }

        $entryPoints = $this->entryPointBuilderFactory->create()
            ->setResponseContext($this->responseContextAccessor->getResponseContext())
            ->setPage($pageModel)
            ->build();

        if ($request = $this->requestStack->getCurrentRequest()) {
            $request->attributes->add([
                'encore_entries' => $entryPoints,
            ]);
        }

        $css = '';
        $headJs = '';
        $bodyJs = '';
        $activeEntryPoints = $entryPoints->allActive();

        foreach ($activeEntryPoints as $entrypoint) {
            if ($hasCssPlaceholder && $entrypoint->requiresCss) {
                $css .= $this->tagRenderer->renderWebpackLinkTags($entrypoint->name);
            }
        }

        foreach ($activeEntryPoints as $entrypoint) {
            if ($hasHeadPlaceholder && $entrypoint->head) {
                $headJs .= $this->tagRenderer->renderWebpackScriptTags(
                    entryName: $entrypoint->name,
                    extraAttributes: $entrypoint->getScriptExtraAttributes(),
                );
            }
        }

        foreach ($activeEntryPoints as $entrypoint) {
            if ($hasBodyPlaceholder && !$entrypoint->head) {
                $bodyJs .= $this->tagRenderer->renderWebpackScriptTags(
                    entryName: $entrypoint->name,
                    extraAttributes: $entrypoint->getScriptExtraAttributes(),
                );
            }
        }

        $this->globalContaoAsset->cleanGlobalArrayFromConfiguration();

        $replace = [];
        $replace["[[TL_CSS$nonce]]"] = "[[TL_CSS$nonce]]" . trim($css);
        $replace["[[TL_HEAD$nonce]]"] = trim($headJs) . "[[TL_HEAD$nonce]]";
        $replace["[[TL_BODY$nonce]]"] = trim($bodyJs) . "[[TL_BODY$nonce]]";

        return str_replace(array_keys($replace), $replace, $buffer);
    }
}
