<?php

namespace HeimrichHannot\EncoreBundle\EntryPoint;

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

    public function getScriptExtraAttributes(): array
    {
        $attributes = [];

        if (is_bool($this->defer)) {
            $attributes['defer'] = $this->defer;
        }

        return $attributes;
    }
}
