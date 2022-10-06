<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
use Exception;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\EncoreBundle\Helper\ArrayHelper;
use HeimrichHannot\UtilsBundle\Arrays\ArrayUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PageEntrypoints
{
    protected $jsEntries = [];
    protected $cssEntries = [];
    protected $jsHeadEntries = [];
    protected $activeEntries = [];

    protected $initialized = false;

    private ContainerInterface $container;
    private FrontendAsset      $frontendAsset;
    private EntryCollection    $entryCollection;
    private ArrayUtil          $arrayUtil;
    private ModelUtil          $modelUtil;

    /**
     * PageEntrypoints constructor.
     */
    public function __construct(ContainerInterface $container, FrontendAsset $frontendAsset, EntryCollection $entryCollection, ArrayUtil $arrayUtil, ModelUtil $modelUtil)
    {
        $this->container = $container;
        $this->frontendAsset = $frontendAsset;
        $this->entryCollection = $entryCollection;
        $this->arrayUtil = $arrayUtil;
        $this->modelUtil = $modelUtil;
    }

    public function generatePageEntrypoints(PageModel $page, LayoutModel $layout, ?string $encoreField = null): bool
    {
        if ($this->initialized) {
            trigger_error('PageEntrypoints already initialized, this can lead to unexpected results. Multiple initializations should be avoided. ', \E_USER_WARNING);
        }

        $projectEntries = $this->entryCollection->getEntries();

        if (empty($projectEntries)) {
            return false;
        }

        $pageEntries = $this->collectPageEntries($layout, $page, $encoreField);

        foreach ($pageEntries as $pageEntry) {
            if (isset($pageEntry['active']) && !$pageEntry['active']) {
                continue;
            }
            if (!($entry = $this->arrayUtil->getArrayRowByFieldValue('name', $pageEntry['entry'], $projectEntries))) {
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
        $parentPages = $this->modelUtil->findParentsRecursively('pid', 'tl_page', $currentPage);
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
     * @throws Exception
     */
    public function getJsEntries(): array
    {
        $this->isInitalized();

        return $this->jsEntries;
    }

    /**
     * @throws Exception
     */
    public function getCssEntries(): array
    {
        $this->isInitalized();

        return $this->cssEntries;
    }

    /**
     * @throws Exception
     */
    public function getJsHeadEntries(): array
    {
        $this->isInitalized();

        return $this->jsHeadEntries;
    }

    /**
     * Return all active entrypoints.
     *
     * @throws Exception
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
        return new static($this->container, $this->frontendAsset, $this->entryCollection, $this->arrayUtil, $this->modelUtil);
    }

    /**
     * Check if initialized and throws exception, if not.
     *
     * @throws Exception
     */
    protected function isInitalized(): void
    {
        if (!$this->initialized) {
            throw new Exception('Page entrypoints are not initialized!');
        }
    }
}
