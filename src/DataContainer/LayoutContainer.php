<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\DataContainer;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\LayoutModel;
use Contao\Message;
use HeimrichHannot\UtilsBundle\Util\Utils;

class LayoutContainer
{
    protected array           $bundleConfig;
    protected ContaoFramework $contaoFramework;
    private Utils             $utils;

    /**
     * LayoutContainer constructor.
     */
    public function __construct(array $bundleConfig, Utils $utils, ContaoFramework $contaoFramework)
    {
        $this->bundleConfig = $bundleConfig;
        $this->contaoFramework = $contaoFramework;
        $this->utils = $utils;
    }

    /**
     * @param DataContainer|null $dc
     */
    public function onLoadCallback($dc): void
    {
        if (!$dc
            || !$this->utils->container()->isBackend()
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
