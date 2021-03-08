<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Asset;

class EntrypointCollection
{
    protected $jsEntries = [];
    protected $jsHeadEntries = [];
    protected $cssEntries = [];

    public function addJsEntry(string $name): void
    {
        $this->jsEntries[] = $name;
    }

    public function addJsHeadEntry(string $name): void
    {
        $this->jsHeadEntries[] = $name;
    }

    public function addCssEntry(string $name): void
    {
        $this->cssEntries[] = $name;
    }

    public function getJsEntries(): array
    {
        return $this->jsEntries;
    }

    public function getJsHeadEntries(): array
    {
        return $this->jsHeadEntries;
    }

    public function getCssEntries(): array
    {
        return $this->cssEntries;
    }

    public function getActiveEntries(): array
    {
        return array_merge($this->jsEntries, $this->jsHeadEntries);
    }

    public function getTemplateData(): array
    {
        $templateData = [];
        $templateData['jsEntries'] = $this->getJsEntries();
        $templateData['jsHeadEntries'] = $this->getJsHeadEntries();
        $templateData['cssEntries'] = $this->getCssEntries();

        return $templateData;
    }
}
