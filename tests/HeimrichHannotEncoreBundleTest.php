<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\HeimrichHannotEncoreBundle;

class HeimrichHannotEncoreBundleTest extends ContaoTestCase
{
    public function testCanBeInstantiated()
    {
        $bundle = new HeimrichHannotEncoreBundle();
        $this->assertInstanceOf(HeimrichHannotEncoreBundle::class, $bundle);
    }
}
