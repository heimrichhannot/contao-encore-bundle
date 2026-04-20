<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;

use Contao\LayoutModel;
use Contao\PageModel;
use Symfony\WebpackEncoreBundle\Exception\EntrypointNotFoundException;
use Twig\Environment;
use Twig\Error\RuntimeError;

/**
 * @deprecated Since version 2.2
 */
class TemplateAsset
{
    /**
     * @var LayoutModel
     */
    private $layout;
    /**
     * @var PageModel
     */
    private $page;
    /**
     * @var string|null
     */
    private $entriesField;

    private bool $initialized = false;
    /**
     * @var array
     */
    private $templateData;

    public function __construct(
        private array $bundleConfig,
        private readonly string $webDir,
        private readonly Environment $twig,
        private PageEntrypoints $pageEntrypoints,
    ) {
    }

    public function createInstance(PageModel $pageModel, LayoutModel $layoutModel, ?string $entriesField = null): self
    {
        $instance = new self($this->bundleConfig, $this->webDir, $this->twig, $this->pageEntrypoints);
        $instance->initialize($pageModel, $layoutModel, $entriesField);

        return $instance;
    }

    public function initialize(PageModel $pageModel, LayoutModel $layoutModel, ?string $entriesField = null): void
    {
        $this->page = $pageModel;
        $this->layout = $layoutModel;
        $this->entriesField = $entriesField;
        $this->pageEntrypoints = $this->pageEntrypoints->createInstance();
        $this->templateData = $this->layout->row();

        if (!$this->pageEntrypoints->generatePageEntrypoints($this->page, $this->layout, $this->entriesField)) {
            return;
        }

        // caution: always render head first because of global dependencies like jQuery
        $this->templateData['jsHeadEntries'] = $this->pageEntrypoints->getJsHeadEntries();
        $this->templateData['jsEntries'] = $this->pageEntrypoints->getJsEntries();
        $this->templateData['cssEntries'] = $this->pageEntrypoints->getCssEntries();

        $this->initialized = true;
    }

    /**
     * Return the javascript that should be included in the header region.
     *
     * @throws \Exception
     */
    public function headScriptTags(): string
    {
        return $this->generateTags('encoreScriptsImportsTemplate', 'default_head_js');
    }

    /**
     * Return the javascript tags that should be included in the footer region.
     *
     * @throws \Exception
     */
    public function scriptTags(): string
    {
        return $this->generateTags('encoreScriptsImportsTemplate', 'default_js');
    }

    /**
     * Return the css link tags that should be included in the header region.
     *
     * @return string
     *
     * @throws \Exception
     */
    public function linkTags()
    {
        return $this->generateTags('encoreStylesheetsImportsTemplate', 'default_css');
    }

    /**
     * Return a link tag with inline css.
     *
     * @return bool|string
     *
     * @throws \Exception
     */
    public function inlineCssLinkTag()
    {
        $styleTags = $this->linkTags();

        preg_match_all('@<link rel="stylesheet" href="([^"]+)">@i', $styleTags, $matches);

        if (!empty($matches[1])) {
            $inlineCss = implode("\n", array_map(fn ($path) => file_get_contents($this->webDir . preg_replace('@<link rel="stylesheet" href="([^"]+)">@i', '$1', $path)), $matches[1]));

            return $inlineCss;
        }

        return false;
    }

    protected function getItemTemplateByName(string $name)
    {
        if (!isset($this->bundleConfig['templates']['imports'])) {
            return null;
        }

        $templates = $this->bundleConfig['templates']['imports'];

        foreach ($templates as $template) {
            if ($template['name'] == $name) {
                return $template['template'];
            }
        }

        return null;
    }

    private function generateTags(string $layoutField, string $defaultTemplate)
    {
        if (!$this->initialized) {
            throw new \Exception('TemplateAsset not initialized!');
        }

        try {
            return $this->twig->render(
                $this->getItemTemplateByName($this->layout->{$layoutField} ?: $defaultTemplate), $this->templateData
            );
        } catch (RuntimeError $e) {
            if ($previous = $e->getPrevious()) {
                if ($previous instanceof EntrypointNotFoundException) {
                    throw new EntrypointNotFoundException($previous->getMessage() . ' Maybe you forgot to run prepare or encore command?', $previous->getCode());
                }
            }
            throw $e;
        }
    }
}
