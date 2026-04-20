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
use HeimrichHannot\EncoreBundle\Event\EncoreEnabledEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

    public function __construct(
        RequestStack $requestStack,
        ParameterBagInterface $parameterBag,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly ContaoFramework $contaoFramework,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        $this->requestStack = $requestStack;
        $this->bundleConfig = $parameterBag->has('huh_encore') ? $parameterBag->get('huh_encore') : [];
        $this->webDir = $parameterBag->has('contao.web_dir') ? $parameterBag->get('contao.web_dir') : '';
    }

    /**
     * Check if encore is enabled on the current page.
     *
     * @deprecated
     */
    public function isEnabledOnCurrentPage(?PageModel $pageModel = null, ?LayoutModel $layout = null): bool
    {
        trigger_deprecation(
            'heimrichhannot/contao-encore-bundle',
            '2.2.0',
            'The method "isEnabledOnCurrentPage" is deprecated since version 2.2.0 and will be removed in version 3.0.0. Please use "isEnabledOnPage" instead.'
        );

        $pageModel ??= $this->getPageModel();

        if (null === $pageModel) {
            return false;
        }

        return $this->isEnabledOnPage($pageModel, $layout);
    }

    public function isEnabledOnPage(PageModel $page, ?LayoutModel $layout = null): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request || !$this->scopeMatcher->isFrontendRequest($request)) {
            return false;
        }

        if (!$layout) {
            $page->loadDetails();
            $layout = $this->contaoFramework
                ->getAdapter(LayoutModel::class)
                ->findByPk($page->layoutId ?? $page->layout);
        }

        if (!$layout?->addEncore) {
            return false;
        }

        if ('modern' !== $layout->type) {
            if (false === $this->evaluateIsEnabled($page, $request)) {
                return false;
            }
        }

        /** @var EncoreEnabledEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new EncoreEnabledEvent(true, $request, $page, $layout)
        );

        return $event->enabled;
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

    private function evaluateIsEnabled(?PageModel $pageModel, Request $request): bool
    {
        $parentPageModel = $this->getPageModel();

        // Check if error page
        if (null !== $this->requestStack->getParentRequest()) {
            if (!$parentPageModel || !\in_array($parentPageModel->type, ['error_401', 'error_403', 'error_404', 'error_503'], true)) {
                return false;
            }
        }

        if (!$pageModel && $parentPageModel) {
            $pageModel = $parentPageModel;
        }

        if (!$pageModel) {
            return false;
        }

        return true;
    }
}
