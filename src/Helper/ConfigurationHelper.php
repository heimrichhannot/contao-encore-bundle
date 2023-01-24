<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Helper;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\LayoutModel;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\PathUtil\Path;

class ConfigurationHelper
{
    /**
     * @var RequestStack
     */
    protected $requestStack;
    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var string
     */
    protected $webDir;
    private ScopeMatcher    $scopeMatcher;
    private ContaoFramework $contaoFramework;

    public function __construct(RequestStack $requestStack, array $bundleConfig, string $webDir, ScopeMatcher $scopeMatcher, ContaoFramework $contaoFramework)
    {
        $this->requestStack = $requestStack;
        $this->bundleConfig = $bundleConfig;
        $this->webDir = $webDir;
        $this->scopeMatcher = $scopeMatcher;
        $this->contaoFramework = $contaoFramework;
    }

    /**
     * Check if encore is enabled on the current page.
     */
    public function isEnabledOnCurrentPage(?PageModel $pageModel = null): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$this->scopeMatcher->isFrontendRequest($request)) {
            return false;
        }

        $parentPageModel = $this->getPageModel();

        // Check if error page
        if (null !== $this->requestStack->getParentRequest()) {
            if (!$parentPageModel || !\in_array($parentPageModel->type, ['error_401', 'error_403', 'error_404', 'error_503'], true)) {
                return false;
            }
        }

        if (null === $pageModel) {
            $pageModel = $parentPageModel;
        }

        if (!$pageModel) {
            return false;
        }

        $layout = $this->contaoFramework->getAdapter(LayoutModel::class)->findByPk($pageModel->layoutId);

        if (!$layout || !$layout->addEncore) {
            return false;
        }

        return true;
    }

    /**
     * Return the relative path to the encore output folder.
     */
    public function getRelativeOutputPath(): string
    {
        return Path::makeRelative($this->bundleConfig['outputPath'], $this->webDir);
    }

    /**
     * Return the absolute path to the encore output folder.
     */
    public function getAbsoluteOutputPath(): string
    {
        return $this->bundleConfig['outputPath'];
    }

    public function getPageModel(): ?PageModel
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request || !$request->attributes->has('pageModel')) {
            return null;
        }

        $pageModel = $request->attributes->get('pageModel');

        if ($pageModel instanceof PageModel) {
            return $pageModel;
        }

        if (
            isset($GLOBALS['objPage'])
            && $GLOBALS['objPage'] instanceof PageModel
            && (int) $GLOBALS['objPage']->id === (int) $pageModel
        ) {
            return $GLOBALS['objPage'];
        }

        return $this->contaoFramework->getAdapter(PageModel::class)->findByPk((int) $pageModel);
    }
}
