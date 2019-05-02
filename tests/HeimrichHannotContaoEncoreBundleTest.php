<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
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