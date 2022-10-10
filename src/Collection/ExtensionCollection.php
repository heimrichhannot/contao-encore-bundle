<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Collection;

use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;

class ExtensionCollection
{
    /** @var array|EncoreExtensionInterface[] */
    protected array $extensions = [];

    /**
     * @return array|EncoreExtensionInterface[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function addExtension(EncoreExtensionInterface $extension): void
    {
        $this->extensions[] = $extension;
    }
}
