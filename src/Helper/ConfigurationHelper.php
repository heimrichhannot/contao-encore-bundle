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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
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
    private ScopeMatcher             $scopeMatcher;
    private ContaoFramework          $contaoFramework;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(RequestStack $requestStack, ParameterBagInterface $parameterBag, ScopeMatcher $scopeMatcher, ContaoFramework $contaoFramework, EventDispatcherInterface $eventDispatcher)
    {
        $this->requestStack = $requestStack;
        $this->bundleConfig = $parameterBag->has('huh_encore') ? $parameterBag->get('huh_encore') : [];
        $this->webDir = $parameterBag->has('contao.web_dir') ? $parameterBag->get('contao.web_dir') : '';
        $this->scopeMatcher = $scopeMatcher;
        $this->contaoFramework = $contaoFramework;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Check if encore is enabled on the current page.
     */
    public function isEnabledOnCurrentPage(?PageModel $pageModel = null): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return false;
        }

        $result = $this->evaluateIsEnabled($pageModel, $request);

        /** @var EncoreEnabledEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new EncoreEnabledEvent($result, $request, $pageModel)
        );

        return $event->isEnabled();
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
        if (!$this->scopeMatcher->isFrontendRequest($request)) {
            return false;
        }

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

        $pageModel->loadDetails();
        $layout = $this->contaoFramework->getAdapter(LayoutModel::class)->findByPk($pageModel->layoutId ?? $pageModel->layout);

        if (!$layout || !$layout->addEncore) {
            return false;
        }

        return true;
    }
}
