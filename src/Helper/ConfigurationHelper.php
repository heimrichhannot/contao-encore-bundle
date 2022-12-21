<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Helper;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\PageModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\PathUtil\Path;

class ConfigurationHelper
{
    /**
     * @var RequestStack
     */
    protected $requestStack;
    /**
     * @var ModelUtil
     */
    protected $modelUtil;
    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var string
     */
    protected            $webDir;
    private ScopeMatcher $scopeMatcher;

    public function __construct(RequestStack $requestStack, ModelUtil $modelUtil, array $bundleConfig, string $webDir, ScopeMatcher $scopeMatcher)
    {
        $this->requestStack = $requestStack;
        $this->modelUtil = $modelUtil;
        $this->bundleConfig = $bundleConfig;
        $this->webDir = $webDir;
        $this->scopeMatcher = $scopeMatcher;
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

        if (null !== $this->requestStack->getParentRequest()) {

            if (
                !($pageModel = $request->attributes->get('pageModel'))
                || !in_array($pageModel->type, ['error_401', 'error_403', 'error_404', 'error_503'])
            ) {
                return false;
            }
        }

        if (null === $pageModel) {
            global $objPage;
            $pageModel = $objPage;
        }

        if (!$pageModel) {
            return false;
        }

        $layout = $this->modelUtil->findModelInstanceByPk('tl_layout', $pageModel->layoutId);
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
}
