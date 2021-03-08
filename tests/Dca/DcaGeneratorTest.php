<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Dca;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Dca\DcaGenerator;
use Symfony\Component\Translation\TranslatorInterface;

class DcaGeneratorTest extends ContaoTestCase
{
    public function testGetEncoreEntriesSelect()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $instance = new DcaGenerator($translator);

        $result = $instance->getEncoreEntriesSelect();
        $this->assertArrayHasKey('label', $result);
        $this->assertArrayHasKey('inputType', $result);
        $this->assertTrue(isset($result['eval']['multiColumnEditor']));
        $this->assertTrue(isset($result['eval']['multiColumnEditor']['fields']['entry']));
        $this->assertTrue(!isset($result['eval']['multiColumnEditor']['fields']['active']));
        $this->assertSame('huh.encore.fields.encoreEntriesSelect.name', $result['label'][0]);
        $this->assertSame('huh.encore.fields.encoreEntriesSelect_entry.description', $result['eval']['multiColumnEditor']['fields']['entry']['label'][1]);

        $result = $instance->getEncoreEntriesSelect(true);
        $this->assertArrayHasKey('label', $result);
        $this->assertArrayHasKey('inputType', $result);
        $this->assertTrue(isset($result['eval']['multiColumnEditor']));
        $this->assertTrue(isset($result['eval']['multiColumnEditor']['fields']['entry']));
        $this->assertTrue(isset($result['eval']['multiColumnEditor']['fields']['active']));
    }
}
