<?php

namespace HeimrichHannot\EncoreBundle\Request\ResponseContext;

class EntryBag
{
    /**
     * @var Entry[]
     */
    private array $entries = [];

    public function addEntry(Entry $entry): self
    {
        $this->entries[$entry->name] = $entry;

        return $this;
    }

    public function getEntry(string $name): ?Entry
    {
        return $this->entries[$name] ?? null;
    }

    public function all(): array
    {
        return $this->entries;
    }
}
