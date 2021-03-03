<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\DataContainer;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\LayoutModel;
use Contao\Message;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class LayoutContainer
{
    /**
     * @var ContainerUtil
     */
    protected $containerUtil;
    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var ModelUtil
     */
    protected $modelUtil;
    /**
     * @var ContaoFrameworkInterface
     */
    protected $contaoFramework;

    /**
     * LayoutContainer constructor.
     */
    public function __construct(array $bundleConfig, ContainerUtil $containerUtil, ModelUtil $modelUtil, ContaoFrameworkInterface $contaoFramework)
    {
        $this->containerUtil = $containerUtil;
        $this->bundleConfig = $bundleConfig;
        $this->modelUtil = $modelUtil;
        $this->contaoFramework = $contaoFramework;
    }

    /**
     * @param DataContainer|null $dc
     */
    public function onLoadCallback($dc): void
    {
        if (!$dc
            || !$this->containerUtil->isBackend()
            || !isset($this->bundleConfig['use_contao_template_variables'])
            || true !== $this->bundleConfig['use_contao_template_variables']
            || !($layout = $this->modelUtil->findModelInstanceByPk(LayoutModel::getTable(), $dc->id))) {
            return;
        }

        if ($layout->addEncore && $layout->addJQuery && (!isset($this->bundleConfig['unset_jquery']) || true !== $this->bundleConfig['unset_jquery'])) {
            $this->contaoFramework->getAdapter(Message::class)->addInfo(($GLOBALS['TL_LANG']['tl_layout']['INFO']['jquery_order_conflict'] ?: ''));
        }
    }
}
