<?php

namespace HeimrichHannot\EncoreBundle\Entrypoints;

class Entrypoint
{
    public function __construct(
        public readonly string $name,
        public readonly bool   $active = true,
        public readonly bool   $head = false,
        public readonly bool   $requiresCss = false,
    ) {}

    public static function fromArray(array $config, bool $active = true): Entrypoint
    {
        return new self(
            name: $config['name'],
            active: $active,
            head: (bool)$config['head'] ?? true,
            requiresCss: (bool)$config['requiresCss'] ?? true,
        );
    }
}