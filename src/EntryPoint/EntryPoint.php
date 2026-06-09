<?php

namespace HeimrichHannot\EncoreBundle\EntryPoint;

use HeimrichHannot\EncoreContracts\EncoreEntry;

class EntryPoint
{
    public function __construct(
        public readonly string $name,
        public readonly bool $active = true,
        public readonly bool $head = false,
        public readonly bool $requiresCss = false,
        public readonly string $origin = '',
        public readonly string $extension = '',
        public readonly ?bool $defer = null,
    ) {
    }

    public static function fromEncoreEntry(EncoreEntry $entry, bool $active, string $origin, string $extension = ''): self
    {
        return new EntryPoint(
            name: $entry->name,
            active: $active,
            head: $entry->isHeadScript,
            requiresCss: $entry->requiresCss,
            origin: $origin,
            extension: $extension,
            defer: $entry->defer,
        );
    }

    public function getScriptExtraAttributes(): array
    {
        $attributes = [];

        if (is_bool($this->defer)) {
            $attributes['defer'] = $this->defer;
        }

        return $attributes;
    }
}
