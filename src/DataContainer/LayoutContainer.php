<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\DataContainer;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\DataContainer;
use Contao\LayoutModel;
use Contao\Message;
use Symfony\Component\HttpFoundation\RequestStack;

class LayoutContainer
{
    protected array           $bundleConfig;
    protected ContaoFramework $contaoFramework;
    private RequestStack      $requestStack;
    private ScopeMatcher      $scopeMatcher;

    /**
     * LayoutContainer constructor.
     */
    public function __construct(array $bundleConfig, ContaoFramework $contaoFramework, RequestStack $requestStack, ScopeMatcher $scopeMatcher)
    {
        $this->bundleConfig = $bundleConfig;
        $this->contaoFramework = $contaoFramework;
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
    }

    public function onLoadCallback(DataContainer $dc = null): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request
            || !$dc
            || !$this->scopeMatcher->isBackendRequest($request)
            || !isset($this->bundleConfig['use_contao_template_variables'])
            || true !== $this->bundleConfig['use_contao_template_variables']
            || !($layout = $this->contaoFramework->getAdapter(LayoutModel::class)->findByPk($dc->id))) {
            return;
        }

        if ($layout->addEncore && $layout->addJQuery && (!isset($this->bundleConfig['unset_jquery']) || true !== $this->bundleConfig['unset_jquery'])) {
            $this->contaoFramework->getAdapter(Message::class)->addInfo(($GLOBALS['TL_LANG']['tl_layout']['INFO']['jquery_order_conflict'] ?: ''));
        }
    }
}
