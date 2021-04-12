<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Helper;

use Contao\PageModel;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\PathUtil\Path;

class ConfigurationHelper
{
    /**
     * @var ContainerUtil
     */
    protected $containerUtil;
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
    protected $webDir;

    public function __construct(ContainerUtil $containerUtil, RequestStack $requestStack, ModelUtil $modelUtil, array $bundleConfig, string $webDir)
    {
        $this->containerUtil = $containerUtil;
        $this->requestStack = $requestStack;
        $this->modelUtil = $modelUtil;
        $this->bundleConfig = $bundleConfig;
        $this->webDir = $webDir;
    }

    /**
     * Check if encore is enabled on the current page.
     */
    public function isEnabledOnCurrentPage(?PageModel $pageModel = null): bool
    {
        if (!$this->containerUtil->isFrontend()) {
            return false;
        }

        if (null !== $this->requestStack->getParentRequest()) {
            return false;
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
