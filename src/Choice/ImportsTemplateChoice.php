<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Choice;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

/**
 * @deprecated This class is deprecated since 1.19.0.
 * @codeCoverageIgnore
 */
class ImportsTemplateChoice extends AbstractChoice
{
    /**
     * @var array
     */
    private $bundleConfig;

    public function __construct(array $bundleConfig, ContaoFrameworkInterface $framework)
    {
        parent::__construct($framework);
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        if (!isset($this->bundleConfig['templates']['imports'])) {
            return $choices;
        }

        foreach ($this->bundleConfig['templates']['imports'] as $template) {
            $choices[$template['name']] = $template['template'];
        }

        asort($choices);

        return $choices;
    }
}
