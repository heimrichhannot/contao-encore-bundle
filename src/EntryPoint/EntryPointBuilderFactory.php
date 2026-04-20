<?php

namespace HeimrichHannot\EncoreBundle\EntryPoint;

use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\UtilsBundle\Util\Utils;

class EntryPointBuilderFactory
{
    public function __construct(
        private readonly Utils $utils,
        private readonly EntryCollection $entryCollection,
    ) {}

    public function create(): EntryPointsBuilder
    {
        return new EntryPointsBuilder(
            $this->utils,
            $this->entryCollection,
        );
    }
}