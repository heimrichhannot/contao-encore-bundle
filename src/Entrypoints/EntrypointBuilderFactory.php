<?php

namespace HeimrichHannot\EncoreBundle\Entrypoints;

use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;
use HeimrichHannot\EncoreBundle\Collection\EntryCollection;
use HeimrichHannot\UtilsBundle\Util\Utils;

class EntrypointBuilderFactory
{
    public function __construct(
        private readonly Utils $utils,
        private readonly FrontendAsset $frontendAsset,
        private readonly EntryCollection $entryCollection,
    ) {}

    public function create(): EntrypointsBuilder
    {
        return new EntrypointsBuilder(
            $this->utils,
            $this->frontendAsset,
            $this->entryCollection,
        );
    }
}