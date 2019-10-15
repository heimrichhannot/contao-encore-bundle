<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\EncoreBundle\Asset;


use Contao\LayoutModel;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PageEntrypoints
{
    /**
     * @var array
     */
    private $bundleConfig;

    protected $jsEntries = [];
    protected $cssEntries = [];
    protected $jsHeadEntries = [];
    /**
     * @var EntrypointsJsonLookup
     */
    private $entrypointsJsonLookup;

    protected $initialized = false;

    protected $activeEntrypoints = [];
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
     * @param array $bundleConfig
     * @param EntrypointsJsonLookup $entrypointsJsonLookup
     */
    public function __construct(array $bundleConfig, EntrypointsJsonLookup $entrypointsJsonLookup, ContainerInterface $container, FrontendAsset $frontendAsset)
    {
        $this->bundleConfig = $bundleConfig['encore'];
        $this->entrypointsJsonLookup = $entrypointsJsonLookup;
        $this->container = $container;
        $this->frontendAsset = $frontendAsset;
    }


    public function addActiveEntrypoint(string $entrypoint)
    {
        $this->activeEntrypoints[] = $entrypoint;
    }

    /**
     * @param PageModel $page
     * @param LayoutModel $layout
     * @param string|null $encoreField
     * @return bool
     */
    public function generatePageEntrypoints(PageModel $page, LayoutModel $layout, ?string $encoreField): bool
    {
        // add entries from the entrypoints.json
        if (isset($this->bundleConfig['entrypointsJsons'])
            && \is_array($this->bundleConfig['entrypointsJsons'])
            && !empty($this->bundleConfig['entrypointsJsons'])
        ) {
            if (!isset($this->bundleConfig['entries']))
            {
                $this->bundleConfig['entries'] = [];
            } elseif (!\is_array($this->bundleConfig['entries']))
            {
                return false;
            }

            $this->bundleConfig['entries'] = $this->entrypointsJsonLookup->mergeEntries(
                $this->bundleConfig['entrypointsJsons'],
                $this->bundleConfig['entries'],
                $layout->encoreBabelPolyfillEntryName,
                $layout
            );
        }

        if (!isset($this->bundleConfig['entries']) || !\is_array($this->bundleConfig['entries']) || empty($this->bundleConfig['entries']))
        {
            return false;
        }

        foreach ($this->bundleConfig['entries'] as $entry)
        {
            if (!isset($entry['name']))
            {
                continue;
            }
            if ($this->isEntryActive($entry['name'], $layout, $page, $encoreField))
            {
                if (isset($entry['head']) && $entry['head'])
                {
                    $this->jsHeadEntries[] = $entry['name'];
                } else
                {
                    $this->jsEntries[] = $entry['name'];
                }

                if (isset($entry['requiresCss']) && $entry['requiresCss'])
                {
                    $this->cssEntries[] = $entry['name'];
                }
            }
        }
        $this->initialized = true;
        return true;
    }

    /**
     * @param string $entry
     *
     * @param LayoutModel $layout
     * @param PageModel $currentPage
     * @param string $encoreField
     * @return bool
     */
    public function isEntryActive(string $entry, LayoutModel $layout, PageModel $currentPage, string $encoreField = 'encoreEntries'): bool
    {
        if ($this->frontendAsset->isActiveEntrypoint($entry)) {
            return true;
        }
        $parents = [$layout];

        $parentPages = $this->container->get('huh.utils.model')->findParentsRecursively('pid', 'tl_page', $currentPage);
        if (\is_array($parentPages)) {
            $parents = array_merge($parents, $parentPages);
        }

        $result = false;

        foreach (array_merge($parents, [$currentPage]) as $i => $page) {
            $isActive = $this->isEntryActiveForPage($entry, $page, $encoreField);

            if (0 == $i || null !== $isActive) {
                $result = $isActive;
            }
        }

        return $result ? true : false;
    }

    /**
     * @param string $entry
     * @param Model $page
     *
     * @param string $encoreField
     * @return bool|null Returns null, if no information about the entry is specified in the page; else bool
     */
    public function isEntryActiveForPage(string $entry, Model $page, string $encoreField = 'encoreEntries')
    {
        $entries = StringUtil::deserialize($page->{$encoreField}, true);

        foreach ($entries as $row) {
            if ($row['entry'] === $entry) {
                if ($page instanceof LayoutModel) {
                    return true;
                }
                return $row['active'] ? true : false;
            }
        }
        return null;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getJsEntries(): array
    {
        $this->isInitalized();
        return $this->jsEntries;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getCssEntries(): array
    {
        $this->isInitalized();
        return $this->cssEntries;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getJsHeadEntries(): array
    {
        $this->isInitalized();
        return $this->jsHeadEntries;
    }

    /**
     * Check if initialized and throws exception, if not.
     *
     * @throws \Exception
     */
    protected function isInitalized(): void
    {
        if (!$this->initialized)
        {
            throw new \Exception("Page entrypoints are not initialized!");
        }
    }

}