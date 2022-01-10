<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use HeimrichHannot\EncoreBundle\Helper\EntryHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @deprecated This class is outdated and will be removed in next major version
 * @codeCoverageIgnore
 */
class GeneratePageListener
{
    /**
     * @var array
     */
    private $bundleConfig;
    /**
     * @var TemplateAsset
     */
    private $templateAsset;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     */
    public function __construct(array $bundleConfig, ContainerInterface $container, TemplateAsset $templateAsset)
    {
        $this->container = $container;
        $this->templateAsset = $templateAsset;
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Exception
     *
     * @deprecated This method is not used anymore and will be removed in next major release
     * @codeCoverageIgnore
     */
    public function addEncore(PageModel $page, LayoutModel $layout, PageRegular $pageRegular, ?string $encoreField = 'encoreEntries', bool $includeInline = false): void
    {
        $templateAssets = $this->templateAsset->createInstance($page, $layout, $encoreField);

        // render css alone (should be used in <head>)
        $pageRegular->Template->encoreStylesheets = $templateAssets->linkTags();

        if ($includeInline) {
            $pageRegular->Template->encoreStylesheetsInline = $templateAssets->inlineCssLinkTag();
        }

        // caution: always render head first because of global dependencies like jQuery
        $pageRegular->Template->encoreHeadScripts = $templateAssets->headScriptTags();

        // render js alone (should be used in footer region)
        $pageRegular->Template->encoreScripts = $templateAssets->scriptTags();
    }

    /**
     * Clean up contao global asset arrays.
     *
     * @deprecated Use EntryHelper::cleanGlobalArrays() instead
     * @codeCoverageIgnore
     */
    public function cleanGlobalArrays(LayoutModel $layout)
    {
        if (!$this->container->get('huh.utils.container')->isFrontend()) {
            return;
        }

        EntryHelper::cleanGlobalArrays($this->bundleConfig);
    }
}
