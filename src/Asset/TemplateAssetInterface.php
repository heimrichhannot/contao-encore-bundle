<?php

namespace HeimrichHannot\EncoreBundle\Asset;

use Contao\LayoutModel;
use Contao\PageModel;

interface TemplateAssetInterface
{
    public function createInstance(PageModel $pageModel, LayoutModel $layoutModel, ?string $entriesField = null): \HeimrichHannot\EncoreBundle\Asset\TemplateAsset;

    public function initialize(PageModel $pageModel, LayoutModel $layoutModel, ?string $entriesField = null);

    /**
     * Return the javascript that should be included in the header region.
     *
     * @throws \Exception
     */
    public function headScriptTags(): string;

    /**
     * Return the javascript tags that should be included in the footer region.
     *
     * @throws \Exception
     */
    public function scriptTags(): string;

    /**
     * Return the css link tags that should be included in the header region.
     *
     * @return string
     * @throws \Exception
     *
     */
    public function linkTags();

    /**
     * Return a link tag with inline css.
     *
     * @return bool|string
     * @throws \Exception
     *
     */
    public function inlineCssLinkTag();
}