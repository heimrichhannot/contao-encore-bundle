<?php

namespace HeimrichHannot\EncoreBundle\Test\EventListener\DcaField;

use HeimrichHannot\EncoreBundle\Dca\EncoreEntriesSelectField;
use HeimrichHannot\EncoreBundle\EventListener\DcaField\EncoreEntriesSelectFieldListener;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class EncoreEntriesSelectFieldListenerTest extends TestCase
{
    public function createTestInstance(): EncoreEntriesSelectFieldListener
    {
        return new EncoreEntriesSelectFieldListener($this->createMock(TranslatorInterface::class));
    }

    public function testOtherTable()
    {
        $GLOBALS['TL_DCA']['tl_test'] = [];
        EncoreEntriesSelectField::register('tl_test');
        $instance = $this->createTestInstance();
        $instance->onLoadDataContainer('tl_example');
        static::assertEmpty($GLOBALS['TL_DCA']['tl_test']);
    }

    public function testDefault()
    {
        $GLOBALS['TL_DCA']['tl_test'] = [];
        EncoreEntriesSelectField::register('tl_test');
        $instance = $this->createTestInstance();
        $instance->onLoadDataContainer('tl_test');

        static::assertTrue(isset($GLOBALS['TL_DCA']['tl_test']['fields']));
        static::assertArrayHasKey('fields', $GLOBALS['TL_DCA']['tl_test']);
        static::assertArrayHasKey('encoreEntries', $GLOBALS['TL_DCA']['tl_test']['fields']);
        static::assertArrayHasKey('entry', $GLOBALS['TL_DCA']['tl_test']['fields']['encoreEntries']['eval']['multiColumnEditor']['fields']);
        static::assertArrayNotHasKey('active', $GLOBALS['TL_DCA']['tl_test']['fields']['encoreEntries']['eval']['multiColumnEditor']['fields']);
    }
    public function testActiveCheckbox()
    {
        $GLOBALS['TL_DCA']['tl_test'] = [];
        EncoreEntriesSelectField::register('tl_test')
            ->setIncludeActiveCheckbox(true);
        $instance = $this->createTestInstance();
        $instance->onLoadDataContainer('tl_test');

        static::assertTrue(isset($GLOBALS['TL_DCA']['tl_test']['fields']));
        static::assertArrayHasKey('fields', $GLOBALS['TL_DCA']['tl_test']);
        static::assertArrayHasKey('encoreEntries', $GLOBALS['TL_DCA']['tl_test']['fields']);
        static::assertArrayHasKey('entry', $GLOBALS['TL_DCA']['tl_test']['fields']['encoreEntries']['eval']['multiColumnEditor']['fields']);
        static::assertArrayHasKey('active', $GLOBALS['TL_DCA']['tl_test']['fields']['encoreEntries']['eval']['multiColumnEditor']['fields']);
    }
}
