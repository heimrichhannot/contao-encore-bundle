<?php

namespace HeimrichHannot\EncoreBundle\Entrypoints;

use HeimrichHannot\UtilsBundle\Util\Utils;

class EntrypointBuilderFactory
{
    public function __construct(
        private readonly Utils $utils,
    ) {}

    public function create(): EntrypointsBuilder
    {
        return new EntrypointsBuilder($this->utils);
    }
}