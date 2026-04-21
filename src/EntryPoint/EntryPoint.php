<?php

namespace HeimrichHannot\EncoreBundle\EntryPoint;

use HeimrichHannot\EncoreBundle\Request\ResponseContext\Entry;

class EntryPoint
{
    public function __construct(
        public readonly string $name,
        public readonly bool $active = true,
        public readonly bool $head = false,
        public readonly bool $requiresCss = false,
        public readonly string $origin = '',
        public readonly string $extension = '',
    ) {
    }
}
