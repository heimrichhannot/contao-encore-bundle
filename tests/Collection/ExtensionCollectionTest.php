<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Collection;

use HeimrichHannot\EncoreBundle\Collection\ExtensionCollection;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;

class ExtensionCollectionTest extends \Contao\TestCase\ContaoTestCase
{
    public function testAddGetExtensions()
    {
        $instance = new ExtensionCollection();
        $this->assertEmpty($instance->getExtensions());

        $instance->addExtension($this->createMock(EncoreExtensionInterface::class));
        $this->assertCount(1, $instance->getExtensions());
    }
}
