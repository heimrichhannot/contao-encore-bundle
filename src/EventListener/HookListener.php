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
use Contao\System;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\String\StringUtil;

class HookListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var ModelUtil
     */
    private $modelUtil;

    /**
     * @var StringUtil
     */
    private $stringUtil;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework, ModelUtil $modelUtil, StringUtil $stringUtil, \Twig_Environment $twig)
    {
        $this->framework = $framework;
        $this->modelUtil = $modelUtil;
        $this->stringUtil = $stringUtil;
        $this->twig      = $twig;
    }

    /**
     * Modify the page object.
     *
     * @param PageModel   $page
     * @param LayoutModel $layout
     * @param PageRegular $pageRegular
     */
    public function addEncore(PageModel $page, LayoutModel $layout, PageRegular $pageRegular)
    {
        global $objPage;
        $stringUtil = $this->stringUtil;

        $rootId = $page->rootId ?: $objPage->id;

        /** @var PageModel $rootPage */
        $rootPage = $this->framework->getAdapter(PageModel::class);

        if (null === ($rootPage = $rootPage->findByPk($rootId)) || !$rootPage->addEncore)
        {
            return;
        }

        $config        = System::getContainer()->getParameter('huh.encore');
        $templateData  = $rootPage->row();
        $templateData['encorePublicPath'] = $stringUtil->removeLeadingAndTrailingSlash($templateData['encorePublicPath']);

        // active entries
        $jsEntries = [];
        $cssEntries = [];

        if (!isset($config['encore']['entries']))
        {
            return;
        }

        foreach ($config['encore']['entries'] as $entry)
        {
            if ($this->computeActiveEntries($entry['name']))
            {
                $jsEntries[] = $this->stringUtil->removeLeadingAndTrailingSlash($rootPage->encorePublicPath) . '/' . $entry['name'] . '.js';

                if ($entry['requiresCss'])
                {
                    $cssEntries[] = $this->stringUtil->removeLeadingAndTrailingSlash($rootPage->encorePublicPath) . '/' . $entry['name'] . '.css';
                }
            }
        }

        $templateData['jsEntries'] = $jsEntries;
        $templateData['cssEntries'] = $cssEntries;

        // render css alone (should be used in <head>)
        $pageRegular->Template->encoreStylesheets = $this->twig->render(
            $this->getItemTemplateByName($rootPage->encoreStylesheetsImportsTemplate ?: 'default_css'), $templateData
        );

        // render js alone (should be used to render js in footer region)
        $pageRegular->Template->encoreScripts = $this->twig->render(
            $this->getItemTemplateByName($rootPage->encoreScriptsImportsTemplate ?: 'default_js'), $templateData
        );
    }

    /**
     * @param string $entry
     *
     * @return bool
     */
    public function computeActiveEntries(string $entry): bool
    {
        global $objPage;

        $parentPages = $this->modelUtil->findParentsRecursively('pid', 'tl_page', $objPage);

        $result = false;

        foreach (array_merge(is_array($parentPages) ? $parentPages : [], [$objPage]) as $i => $page)
        {
            $isActive = $this->isEntryActiveForPage($entry, $page);

            if (0 == $i || $isActive !== null)
            {
                $result = $isActive;
            }
        }

        return $result ? true : false;
    }

    /**
     * @param string $entry
     * @param Model  $page
     *
     * @return null|bool Returns null, if no information about the entry is specified in the page; else bool
     */
    public function isEntryActiveForPage(string $entry, Model $page)
    {
        $entries = \Contao\StringUtil::deserialize($page->encoreEntries, true);

        foreach ($entries as $row)
        {
            if ($row['entry'] === $entry)
            {
                return $row['active'] ? true : false;
            }

        }

        return null;
    }

    public function cleanGlobalArrays()
    {
        global $objPage;

        /** @var PageModel $rootPage */
        $rootPage = $this->framework->getAdapter(PageModel::class);

        if (null === ($rootPage = $rootPage->findByPk($objPage->rootId)) || !$rootPage->addEncore)
        {
            return;
        }

        $config = System::getContainer()->getParameter('huh.encore');

        // js
        if (isset($config['encore']['legacy']['js']) && is_array($config['encore']['legacy']['js']))
        {
            $jsFiles = &$GLOBALS['TL_JAVASCRIPT'];

            if (is_array($jsFiles)) {
                foreach ($config['encore']['legacy']['js'] as $jsFile)
                {
                    if (isset($jsFiles[$jsFile]))
                    {
                        unset($jsFiles[$jsFile]);
                    }
                }
            }
        }

        // css
        if (isset($config['encore']['legacy']['css']) && is_array($config['encore']['legacy']['css']))
        {
            foreach (['TL_USER_CSS', 'TL_CSS'] as $arrayKey)
            {
                $cssFiles = &$GLOBALS[$arrayKey];

                if (is_array($cssFiles)) {
                    foreach ($config['encore']['legacy']['css'] as $cssFile)
                    {
                        if (isset($cssFiles[$cssFile]))
                        {
                            unset($cssFiles[$cssFile]);
                        }
                    }
                }
            }
        }
    }

    public function getItemTemplateByName(string $name)
    {
        $config = System::getContainer()->getParameter('huh.encore');

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