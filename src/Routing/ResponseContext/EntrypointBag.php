<?php

namespace HeimrichHannot\EncoreBundle\Routing\ResponseContext;

use HeimrichHannot\EncoreContracts\EncoreEntry;

class EntrypointBag
{
    /** @var string[] */
    private array $entrypoints = [];

    public function getEntrypoints(): array
    {
        return $this->entrypoints;
    }

    public function addEntrypoint(string $entrypoint): void
    {
        $this->entrypoints[] = $entrypoint;
    }
}