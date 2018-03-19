<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle;

use HeimrichHannot\EncoreBundle\DependencyInjection\EncoreExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoEncoreBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new EncoreExtension();
    }
}
