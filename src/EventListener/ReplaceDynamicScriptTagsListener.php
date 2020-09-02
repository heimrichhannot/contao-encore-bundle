<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\EncoreBundle\EventListener;

use HeimrichHannot\EncoreBundle\Helper\EntryHelper;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;

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
     * ReplaceDynamicScriptTagsListener constructor.
     */
    public function __construct(array $bundleConfig, ContainerUtil $containerUtil)
    {
        $this->bundleConfig = $bundleConfig;
        $this->containerUtil = $containerUtil;
    }

    public function __invoke(string $buffer): string
    {
        if (!$this->containerUtil->isFrontend()) {
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