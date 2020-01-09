<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use HeimrichHannot\EncoreBundle\Asset\EntrypointsJsonLookup;
use HeimrichHannot\EncoreBundle\Asset\TemplateAsset;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

class HookListener
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var TemplateAsset
     */
    private $templateAsset;

    /**
     * Constructor.
     *
     * @param ContainerInterface    $container
     * @param Environment           $twig
     * @param EntrypointsJsonLookup $entrypointsJsonLookup
     */
    public function __construct(ContainerInterface $container, Environment $twig, TemplateAsset $templateAsset)
    {
        $this->framework = $container->get('contao.framework');
        $this->twig = $twig;
        $this->container = $container;
        $this->templateAsset = $templateAsset;
    }

    /**
     * generatePage Hook
     *
     * @param PageModel $page
     * @param LayoutModel $layout
     * @param PageRegular $pageRegular
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function onGeneratePage(PageModel $page, LayoutModel $layout, PageRegular $pageRegular)
    {
        $this->addEncore($page, $layout, $pageRegular);
        $this->cleanGlobalArrays();
    }

    /**
     * A alias for the addEncore method. Just for backward compatibility. Will be removed in next major version.
     *
     * @deprecated Use addEncore method instead. Will be removed in next major release.
     *
     * @param PageModel $page
     * @param LayoutModel $layout
     * @param PageRegular $pageRegular
     * @param string|null $encoreField
     * @param bool $includeInline
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function doAddEncore(PageModel $page, LayoutModel $layout, PageRegular $pageRegular, ?string $encoreField = 'encoreEntries', bool $includeInline = false)
    {
        $this->addEncore($page, $layout, $pageRegular, $encoreField, $includeInline);
    }

    /**
     * @param PageModel $page
     * @param LayoutModel $layout
     * @param PageRegular $pageRegular
     * @param string|null $encoreField
     * @param bool $includeInline
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Exception
     */
    public function addEncore(PageModel $page, LayoutModel $layout, PageRegular $pageRegular, ?string $encoreField = 'encoreEntries', bool $includeInline = false): void
    {
        if (!$layout->addEncore) {
            return;
        }

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

    public function cleanGlobalArrays()
    {
        if (!$this->container->get('huh.utils.container')->isFrontend()) {
            return;
        }

        /* @var PageModel $objPage */
        global $objPage;

        /** @var LayoutModel $layout */
        $layout = $this->framework->getAdapter(LayoutModel::class);

        if (null === ($layout = $layout->findByPk($objPage->layout)) || !$layout->addEncore) {
            return;
        }

        $config = $this->container->getParameter('huh_encore');

        // js
        if (isset($config['legacy']['js']) && \is_array($config['legacy']['js'])) {
            $jsFiles = &$GLOBALS['TL_JAVASCRIPT'];

            if (\is_array($jsFiles)) {
                foreach ($config['legacy']['js'] as $jsFile) {
                    if (isset($jsFiles[$jsFile])) {
                        unset($jsFiles[$jsFile]);
                    }
                }
            }
        }
        // jquery
        if (isset($config['legacy']['jquery']) && \is_array($config['legacy']['jquery'])) {
            $jqueryFiles = &$GLOBALS['TL_JQUERY'];

            if (\is_array($jqueryFiles)) {
                foreach ($config['legacy']['jquery'] as $legacyFile) {
                    if (isset($jqueryFiles[$legacyFile])) {
                        unset($jqueryFiles[$legacyFile]);
                    }
                }
            }
        }

        // css
        if (isset($config['legacy']['css']) && \is_array($config['legacy']['css'])) {
            foreach (['TL_USER_CSS', 'TL_CSS'] as $arrayKey) {
                $cssFiles = &$GLOBALS[$arrayKey];

                if (\is_array($cssFiles)) {
                    foreach ($config['legacy']['css'] as $cssFile) {
                        if (isset($cssFiles[$cssFile])) {
                            unset($cssFiles[$cssFile]);
                        }
                    }
                }
            }
        }
        if (isset($config['legacy']['unset_jquery']) && true === $config['legacy']['unset_jquery']) {
            $jsFiles = &$GLOBALS['TL_JAVASCRIPT'];
            if (($key = array_search('assets/jquery/js/jquery.min.js|static', $jsFiles)) !== false) {
                unset($jsFiles[$key]);
            }
        }
    }
}