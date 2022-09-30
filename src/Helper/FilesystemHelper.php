<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Helper;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class FilesystemHelper
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getPackagePath(string $bundleClass): string
    {
        $reflection = new \ReflectionClass($bundleClass);
        $filesystem = new Filesystem();

        $filesystem->makePathRelative($reflection->getFileName(), $this->kernel->getProjectDir());

        $a = $reflection->getFileName();
    }
}
