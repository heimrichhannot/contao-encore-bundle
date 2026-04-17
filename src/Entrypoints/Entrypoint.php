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

    public static function fromArray(array $config): Entrypoint
    {
        return new self(
            name: $config['name'],
            active: (bool)$config['active'] ?? true,
            head: (bool)$config['head'] ?? true,
            requiresCss: (bool)$config['requiresCss'] ?? true,
        );
    }

    public static function fromString(string $name): Entrypoint
    {
        return new self(name: $name);
    }
}