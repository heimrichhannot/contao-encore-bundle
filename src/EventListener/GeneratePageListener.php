<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use HeimrichHannot\EncoreBundle\Helper\EntryHelper;
use HeimrichHannot\EncoreBundle\Helper\MultilingualHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

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
     * @var Environment
     */
    private $twig;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var \Contao\CoreBundle\Framework\ContaoFramework|object|null
     */
    private $framework;

    /**
     * Constructor.
     */
    public function __construct(array $bundleConfig, ContaoFrameworkInterface $framework, ContainerInterface $container, Environment $twig, TemplateAsset $templateAsset)
    {
        $this->framework = $framework;
        $this->twig = $twig;
        $this->container = $container;
        $this->templateAsset = $templateAsset;
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * @Hook("generatePage")
     */
    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular)
    {
        if (!$layout->addEncore) {
            return;
        }
        $this->addEncore($pageModel, $layout, $pageRegular);
    }

    /**
     * @deprecated Use __invoke instead.
     */
    public function onGeneratePage(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if (!$layout->addEncore) {
            return;
        }
        $this->addEncore($pageModel, $layout, $pageRegular);
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Exception
     */
    public function addEncore(PageModel $page, LayoutModel $layout, PageRegular $pageRegular, ?string $encoreField = 'encoreEntries', bool $includeInline = false): void
    {
        $templateAssets = $this->templateAsset->createInstance($page, $layout, $encoreField);

        // render css alone (should be used in <head>)
        $pageRegular->Template->encoreStylesheets = $templateAssets->linkTags();

        if ($includeInline) {
            $pageRegular->Template->encoreStylesheetsInline = $templateAssets->inlineCssLinkTag();
        }

        // render js alone (should be used in footer region)
        $pageRegular->Template->encoreScripts = $templateAssets->scriptTags();

        $pageRegular->Template->encoreHeadScripts = $templateAssets->headScriptTags();
    }

    /**
     * Clean up contao global asset arrays.
     *
     * @deprecated Use EntryHelper::cleanGlobalArrays() instead
     */
    public function cleanGlobalArrays(LayoutModel $layout)
    {
        if (!$this->container->get('huh.utils.container')->isFrontend()) {
            return;
        }

        EntryHelper::cleanGlobalArrays($this->bundleConfig);
    }
}
