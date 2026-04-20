<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Event;

use Contao\LayoutModel;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class EncoreEnabledEvent extends Event
{
    public function __construct(
        public bool $enabled,
        public readonly Request $request,
        public readonly ?PageModel $pageModel = null,
        public readonly ?LayoutModel $layoutModel = null,
    ) {
    }

    /**
     * @deprecated
     */
    public function isEnabled(): bool
    {
        trigger_deprecation(
            'heimrichhannot/contao-encore-bundle',
            '2.2.0',
            'Use class instead.'
        );

        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @deprecated
     */
    public function getRequest(): Request
    {
        trigger_deprecation(
            'heimrichhannot/contao-encore-bundle',
            '2.2.0',
            'Use class instead.'
        );

        return $this->request;
    }

    /**
     * @deprecated
     */
    public function getPageModel(): ?PageModel
    {
        trigger_deprecation(
            'heimrichhannot/contao-encore-bundle',
            '2.2.0',
            'Use class instead.'
        );

        return $this->pageModel;
    }
}
