<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\EncoreBundle\Test\Asset;


use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Asset\FrontendAsset;

class FrontendAssetTest extends ContaoTestCase
{
    public function testEntrypoints()
    {
        $frontendAsset = new FrontendAsset();

        $frontendAsset->addActiveEntrypoint('contao-encore-bundle');
        $this->assertTrue($frontendAsset->isActiveEntrypoint('contao-encore-bundle'));
        $this->assertFalse($frontendAsset->isActiveEntrypoint('contao-slick-bundle'));
        $this->assertCount(1, $frontendAsset->getActiveEntrypoints());
    }
}