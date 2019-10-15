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
use HeimrichHannot\EncoreBundle\Asset\PageEntrypoints;
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
     * @var PageEntrypoints
     */
    private $pageEntrypoints;

    /**
     * Constructor.
     *
     * @param ContainerInterface    $container
     * @param Environment           $twig
     * @param EntrypointsJsonLookup $entrypointsJsonLookup
     */
    public function __construct(ContainerInterface $container, Environment $twig, PageEntrypoints $pageEntrypoints)
    {
        $this->framework = $container->get('contao.framework');
        $this->twig = $twig;
        $this->container = $container;
        $this->pageEntrypoints = $pageEntrypoints;
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
     * @param PageModel $page
     * @param LayoutModel $layout
     * @param PageRegular $pageRegular
     * @param string|null $encoreField
     * @param bool $includeInline
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function addEncore(PageModel $page, LayoutModel $layout, PageRegular $pageRegular, ?string $encoreField = 'encoreEntries', bool $includeInline = false): void
    {
        if (!$layout->addEncore) {
            return;
        }

        if (!$this->container->hasParameter('huh.encore')) {
            return;
        }

        $config = $this->container->getParameter('huh.encore');
        $templateData = $layout->row();

        if (!$this->pageEntrypoints->generatePageEntrypoints($page, $layout, $encoreField))
        {
            return;
        }

        $templateData['jsEntries'] = $this->pageEntrypoints->getJsEntries();
        $templateData['jsHeadEntries'] = $this->pageEntrypoints->getJsHeadEntries();
        $templateData['cssEntries'] = $this->pageEntrypoints->getCssEntries();

        // render css alone (should be used in <head>)
        $pageRegular->Template->encoreStylesheets = $this->twig->render(
            $this->getItemTemplateByName($layout->encoreStylesheetsImportsTemplate ?: 'default_css'), $templateData
        );

        if ($includeInline) {
            $pageRegular->Template->encoreStylesheetsInline = $this->getInlineStylesheets($pageRegular->Template->encoreStylesheets);
        }

        // render js alone (should be used in footer region)
        $pageRegular->Template->encoreScripts = $this->twig->render(
            $this->getItemTemplateByName($layout->encoreScriptsImportsTemplate ?: 'default_js'), $templateData
        );

        $pageRegular->Template->encoreHeadScripts = $this->twig->render(
            $this->getItemTemplateByName($layout->encoreScriptsImportsTemplate ?: 'default_head_js'), $templateData
        );
    }

    public function getInlineStylesheets(string $styleTags)
    {
        preg_match_all('@<link rel="stylesheet" href="([^"]+)">@i', $styleTags, $matches);

        if (isset($matches[1]) && !empty($matches[1])) {
            $inlineCss = implode("\n", array_map(function ($path) {
                return file_get_contents($this->container->getParameter('contao.web_dir').preg_replace('@<link rel="stylesheet" href="([^"]+)">@i', '$1', $path));
            }, $matches[1]));

            return $inlineCss;
        }

        return false;
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
        if (isset($config['encore']['legacy']['js']) && \is_array($config['encore']['legacy']['js'])) {
            $jsFiles = &$GLOBALS['TL_JAVASCRIPT'];

            if (\is_array($jsFiles)) {
                foreach ($config['encore']['legacy']['js'] as $jsFile) {
                    if (isset($jsFiles[$jsFile])) {
                        unset($jsFiles[$jsFile]);
                    }
                }
            }
        }
        // jquery
        if (isset($config['encore']['legacy']['jquery']) && \is_array($config['encore']['legacy']['jquery'])) {
            $jqueryFiles = &$GLOBALS['TL_JQUERY'];

            if (\is_array($jqueryFiles)) {
                foreach ($config['encore']['legacy']['jquery'] as $legacyFile) {
                    if (isset($jqueryFiles[$legacyFile])) {
                        unset($jqueryFiles[$legacyFile]);
                    }
                }
            }
        }

        // css
        if (isset($config['encore']['legacy']['css']) && \is_array($config['encore']['legacy']['css'])) {
            foreach (['TL_USER_CSS', 'TL_CSS'] as $arrayKey) {
                $cssFiles = &$GLOBALS[$arrayKey];

                if (\is_array($cssFiles)) {
                    foreach ($config['encore']['legacy']['css'] as $cssFile) {
                        if (isset($cssFiles[$cssFile])) {
                            unset($cssFiles[$cssFile]);
                        }
                    }
                }
            }
        }
    }

    public function getItemTemplateByName(string $name)
    {
        $config = $this->container->getParameter('huh.encore');

        if (!isset($config['encore']['templates']['imports'])) {
            return null;
        }

        $templates = $config['encore']['templates']['imports'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['template'];
            }
        }

        return null;
    }
}