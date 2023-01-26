<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Event;

use Contao\PageModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class EncoreEnabledEvent extends Event
{
    private bool    $enabled;
    private Request $request;
    private ?PageModel $pageModel;

    public function __construct(bool $enabled, Request $request, ?PageModel $pageModel)
    {
        $this->enabled = $enabled;
        $this->request = $request;
        $this->pageModel = $pageModel;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getPageModel(): ?PageModel
    {
        return $this->pageModel;
    }
}
