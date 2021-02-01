<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\EventListener;

use HeimrichHannot\EncoreBundle\Helper\EntryHelper;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

/**
 * @Hook("replaceDynamicScriptTags")
 */
class ReplaceDynamicScriptTagsListener
{
    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var ContainerUtil
     */
    protected $containerUtil;
    /**
     * @var ModelUtil
     */
    protected $modelUtil;

    /**
     * ReplaceDynamicScriptTagsListener constructor.
     */
    public function __construct(array $bundleConfig, ContainerUtil $containerUtil, ModelUtil $modelUtil)
    {
        $this->bundleConfig = $bundleConfig;
        $this->containerUtil = $containerUtil;
        $this->modelUtil = $modelUtil;
    }

    public function __invoke(string $buffer): string
    {
        if (!$this->containerUtil->isFrontend()) {
            return $buffer;
        }

        global $objPage;
        $objLayout = $this->modelUtil->findModelInstanceByPk('tl_layout', $objPage->layoutId);
        if (!$objLayout || !$objLayout->addEncore) {
            return $buffer;
        }

        $this->cleanGlobalArrays();

        return $buffer;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function cleanGlobalArrays()
    {
        EntryHelper::cleanGlobalArrays($this->bundleConfig);
    }
}
