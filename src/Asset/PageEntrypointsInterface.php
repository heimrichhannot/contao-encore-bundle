<?php

namespace HeimrichHannot\EncoreBundle\Asset;

use Contao\LayoutModel;
use Contao\PageModel;
use Exception;

interface PageEntrypointsInterface
{
    public function generatePageEntrypoints(PageModel $page, LayoutModel $layout, ?string $encoreField = null): bool;

    /**
     * Collect all entries for the current page.
     *
     * @return array
     */
    public function collectPageEntries(LayoutModel $layout, PageModel $currentPage, ?string $encoreField = null);

    /**
     * @throws Exception
     */
    public function getJsEntries(): array;

    /**
     * @throws Exception
     */
    public function getCssEntries(): array;

    /**
     * @throws Exception
     */
    public function getJsHeadEntries(): array;

    /**
     * Return all active entrypoints.
     *
     * @throws Exception
     */
    public function getActiveEntries(): array;

    /**
     * Return a fresh instance of PageEntryPoint.
     *
     * @return \HeimrichHannot\EncoreBundle\Asset\PageEntrypoints
     */
    public function createInstance();
}