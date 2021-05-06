<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
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
