<?php

namespace HeimrichHannot\EncoreBundle\Request\ResponseContext;

class Entry
{
    public function __construct(
        public readonly string $name,
        public readonly string $origin = '',
        public readonly string $extension = '',
    ) {
    }
}
