<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\HeimrichHannotContaoEncoreBundle;

class HeimrichHannotContaoEncoreBundleTest extends ContaoTestCase
{
    public function testCanBeInstantiated()
    {
        $bundle = new HeimrichHannotContaoEncoreBundle();
        $this->assertInstanceOf(HeimrichHannotContaoEncoreBundle::class, $bundle);
    }
}
