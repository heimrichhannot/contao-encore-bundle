<?php

namespace HeimrichHannot\EncoreBundle\EncoreExtension;

use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;

abstract class AbstractProjectEncoreExtension implements EncoreExtensionInterface
{
    public function getBundle(): string
    {
        return 'App';
    }
}