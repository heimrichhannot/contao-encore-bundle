<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\LayoutModel;
use Contao\Model;
use Contao\PageModel;
use Contao\PageRegular;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

class HookListener
{
    /**
     * @var ContaoFrameworkInterface
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
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContainerInterface $container, Environment $twig)
    {
        $this->framework  = $container->get('contao.framework');
        $this->twig       = $twig;
        $this->container = $container;
    }

    /**
     * Modify the page object.
     *
     * @param PageModel $page
     * @param LayoutModel $layout
     * @param PageRegular $pageRegular
     */
    public function addEncore(PageModel $page, LayoutModel $layout, PageRegular $pageRegular)
    {
        $this->doAddEncore($page, $layout, $pageRegular);
    }

    public function doAddEncore(PageModel $page, LayoutModel $layout, PageRegular $pageRegular, string $encoreField = 'encoreEntries', bool $includeInline = false)
    {
        global $objPage;

        if (!$layout->addEncore) {
            return;
        }

        $config                           = $this->container->getParameter('huh.encore');
        $templateData                     = $layout->row();

        // active entries
        $jsEntries  = [];
        $cssEntries = [];

        if (!isset($config['encore']['entries'])) {
            return;
        }

        foreach ($config['encore']['entries'] as $entry) {
            if ($this->isEntryActive($entry['name'], $layout, $objPage, $encoreField)) {
                if ($entry['head'])
                {
                    $jsHeadEntries[] = $entry['name'];
                }
                else
                {
                    $jsEntries[] = $entry['name'];
                }

                if ($entry['requiresCss']) {
                    $cssEntries[] = $entry['name'];
                }
            }
        }

        $templateData['jsEntries']     = $jsEntries;
        $templateData['jsHeadEntries'] = $jsHeadEntries;
        $templateData['cssEntries']    = $cssEntries;

        // render css alone (should be used in <head>)
        $pageRegular->Template->encoreStylesheets = $this->twig->render(
            $this->getItemTemplateByName($layout->encoreStylesheetsImportsTemplate ?: 'default_css'), $templateData
        );

        if ($includeInline)
        {
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

    public function getInlineStylesheets(string $styleTags) {
        preg_match_all('@<link rel="stylesheet" href="([^"]+)">@i', $styleTags, $matches);

        if (isset($matches[1]) && !empty($matches[1]))
        {
            $inlineCss = implode("\n", array_map(function($path) {
                return file_get_contents($this->container->get('huh.utils.container')->getWebDir() . preg_replace('@<link rel="stylesheet" href="([^"]+)">@i', '$1', $path));
            }, $matches[1]));

            return $inlineCss;
        }

        return false;
    }

    /**
     * @param string $entry
     *
     * @return bool
     */
    public function isEntryActive(string $entry, LayoutModel $layout, PageModel $currentPage, string $encoreField = 'encoreEntries'): bool
    {
        $parents = [$layout];

        $parentPages = $this->container->get('huh.utils.model')->findParentsRecursively('pid', 'tl_page', $currentPage);
        if (is_array($parentPages))
        {
            $parents = array_merge($parents, $parentPages);
        }

        $result = false;

        foreach (array_merge($parents, [$currentPage]) as $i => $page) {
            $isActive = $this->isEntryActiveForPage($entry, $page, $encoreField);

            if (0 == $i || $isActive !== null) {
                $result = $isActive;
            }
        }

        return $result ? true : false;
    }

    /**
     * @param string $entry
     * @param Model $page
     *
     * @return null|bool Returns null, if no information about the entry is specified in the page; else bool
     */
    public function isEntryActiveForPage(string $entry, Model $page, string $encoreField = 'encoreEntries')
    {
        $entries = \Contao\StringUtil::deserialize($page->{$encoreField}, true);

        foreach ($entries as $row) {
            if ($row['entry'] === $entry) {
                return $row['active'] ? true : false;
            }

        }

        return null;
    }

    public function cleanGlobalArrays()
    {
        /** @var PageModel $objPage */
        global $objPage;

        /** @var LayoutModel $layout */
        $layout = $this->framework->getAdapter(LayoutModel::class);

        if (null === ($layout = $layout->findByPk($objPage->layout)) || !$layout->addEncore) {
            return;
        }

        $config = $this->container->getParameter('huh.encore');

        // js
        if (isset($config['encore']['legacy']['js']) && is_array($config['encore']['legacy']['js'])) {
            $jsFiles = &$GLOBALS['TL_JAVASCRIPT'];

            if (is_array($jsFiles)) {
                foreach ($config['encore']['legacy']['js'] as $jsFile) {
                    if (isset($jsFiles[$jsFile])) {
                        unset($jsFiles[$jsFile]);
                    }
                }
            }
        }
        // jquery
        if (isset($config['encore']['legacy']['jquery']) && is_array($config['encore']['legacy']['jquery'])) {
            $jqueryFiles = &$GLOBALS['TL_JQUERY'];

            if (is_array($jqueryFiles)) {
                foreach ($config['encore']['legacy']['jquery'] as $legacyFile) {
                    if (isset($jqueryFiles[$legacyFile])) {
                        unset($jqueryFiles[$legacyFile]);
                    }
                }
            }
        }

        // css
        if (isset($config['encore']['legacy']['css']) && is_array($config['encore']['legacy']['css'])) {
            foreach (['TL_USER_CSS', 'TL_CSS'] as $arrayKey) {
                $cssFiles = &$GLOBALS[$arrayKey];

                if (is_array($cssFiles)) {
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