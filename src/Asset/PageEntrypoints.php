<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
use HeimrichHannot\EncoreBundle\Helper\ArrayHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PageEntrypoints
{
    protected $jsEntries = [];
    protected $cssEntries = [];
    protected $jsHeadEntries = [];
    protected $activeEntries = [];

    protected $initialized = false;
    /**
     * @var array
     */
    private $bundleConfig;
    /**
     * @var EntrypointsJsonLookup
     */
    private $entrypointsJsonLookup;

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var FrontendAsset
     */
    private $frontendAsset;

    /**
     * PageEntrypoints constructor.
     */
    public function __construct(array $bundleConfig, EntrypointsJsonLookup $entrypointsJsonLookup, ContainerInterface $container, FrontendAsset $frontendAsset)
    {
        $this->bundleConfig = $bundleConfig;
        $this->entrypointsJsonLookup = $entrypointsJsonLookup;
        $this->container = $container;
        $this->frontendAsset = $frontendAsset;
    }

    public function generatePageEntrypoints(PageModel $page, LayoutModel $layout, ?string $encoreField = null): bool
    {
        if ($this->initialized) {
            trigger_error('PageEntrypoints already initialized, this can lead to unexpected results. Multiple initializations should be avoided. ', E_USER_WARNING);
        }
        // add entries from the entrypoints.json
        if (isset($this->bundleConfig['entrypoints_jsons'])
            && \is_array($this->bundleConfig['entrypoints_jsons'])
            && !empty($this->bundleConfig['entrypoints_jsons'])
        ) {
            if (!isset($this->bundleConfig['js_entries'])) {
                $this->bundleConfig['js_entries'] = [];
            } elseif (!\is_array($this->bundleConfig['js_entries'])) {
                return false;
            }

            $this->bundleConfig['js_entries'] = $this->entrypointsJsonLookup->mergeEntries(
                $this->bundleConfig['entrypoints_jsons'],
                $this->bundleConfig['js_entries'],
                $layout
            );
        }

        if (!isset($this->bundleConfig['js_entries']) || !\is_array($this->bundleConfig['js_entries']) || empty($this->bundleConfig['js_entries'])) {
            return false;
        }

        $pageEntries = $this->collectPageEntries($layout, $page, $encoreField);
        foreach ($pageEntries as $pageEntry) {
            if (isset($pageEntry['active']) && !$pageEntry['active']) {
                continue;
            }
            if (!($entry = $this->container->get('huh.utils.array')->getArrayRowByFieldValue('name', $pageEntry['entry'], $this->bundleConfig['js_entries']))) {
                continue;
            }
            $this->activeEntries[] = $entry['name'];
            if (isset($entry['head']) && $entry['head']) {
                $this->jsHeadEntries[] = $entry['name'];
            } else {
                $this->jsEntries[] = $entry['name'];
            }

            if (isset($entry['requires_css']) && $entry['requires_css']) {
                $this->cssEntries[] = $entry['name'];
            }
        }

        $this->initialized = true;

        return true;
    }

    /**
     * Collect all entries for the current page.
     *
     * @return array
     */
    public function collectPageEntries(LayoutModel $layout, PageModel $currentPage, ?string $encoreField = null)
    {
        if (null === $encoreField) {
            $encoreField = 'encoreEntries';
        }
        $parents = [$layout];
        $parentPages = $this->container->get('huh.utils.model')->findParentsRecursively('pid', 'tl_page', $currentPage);
        if (\is_array($parentPages)) {
            $parents = array_merge($parents, $parentPages);
        }
        $parents = array_merge($parents, [$currentPage]);
        $parents = array_reverse($parents);

        $pageEntrypointsList = [];
        foreach ($parents as $i => $page) {
            $pageEntrypointsList[] = StringUtil::deserialize($page->{$encoreField}, true);
        }

        $activeEntrypoints = $this->frontendAsset->getActiveEntrypoints();
        array_walk($activeEntrypoints, function (&$value, $key) {
            $value = ['entry' => $value];
        });
        $pageEntrypointsList[] = $activeEntrypoints;

        if ($layout->addEncoreBabelPolyfill && !empty($layout->encoreBabelPolyfillEntryName)) {
            $pageEntrypointsList[] = [['entry' => $layout->encoreBabelPolyfillEntryName]];
        }

        $pageEntrypointsList = array_reverse($pageEntrypointsList);
        $pageEntrypoints = [];
        array_walk($pageEntrypointsList, function ($value, $index) use (&$pageEntrypoints) {
            $pageEntrypoints = array_merge($pageEntrypoints, $value);
        });
        $pageEntrypoints = ArrayHelper::arrayUniqueMultidimensional($pageEntrypoints, 'entry', true);

        return $pageEntrypoints;
    }

    /**
     * @throws \Exception
     */
    public function getJsEntries(): array
    {
        $this->isInitalized();

        return $this->jsEntries;
    }

    /**
     * @throws \Exception
     */
    public function getCssEntries(): array
    {
        $this->isInitalized();

        return $this->cssEntries;
    }

    /**
     * @throws \Exception
     */
    public function getJsHeadEntries(): array
    {
        $this->isInitalized();

        return $this->jsHeadEntries;
    }

    /**
     * Return all active entrypoints.
     *
     * @throws \Exception
     */
    public function getActiveEntries(): array
    {
        $this->isInitalized();

        return $this->activeEntries;
    }

    /**
     * Return a fresh instance of PageEntryPoint.
     *
     * @return $this
     */
    public function createInstance()
    {
        return new static($this->bundleConfig, $this->entrypointsJsonLookup, $this->container, $this->frontendAsset);
    }

    /**
     * Check if initialized and throws exception, if not.
     *
     * @throws \Exception
     */
    protected function isInitalized(): void
    {
        if (!$this->initialized) {
            throw new \Exception('Page entrypoints are not initialized!');
        }
    }
}
