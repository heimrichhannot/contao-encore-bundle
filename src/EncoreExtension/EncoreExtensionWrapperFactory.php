<?php

namespace HeimrichHannot\EncoreBundle\EncoreExtension;

use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class EncoreExtensionWrapperFactory
{
    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
    }

    public function wrap(EncoreExtensionInterface $extension): EncoreExtensionWrapper
    {
        return new EncoreExtensionWrapper($extension, $this->kernel);
    }
}
