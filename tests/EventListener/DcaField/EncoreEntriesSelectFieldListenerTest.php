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
    public function testTableTranslationsTakePrecedenceOverFallbackLabels(): void
    {
        EncoreEntriesSelectField::register('tl_theme');
        $GLOBALS['TL_LANG']['tl_theme']['encoreEntries'] = ['Theme entries', 'Inherited by layouts.'];
        $this->createTestInstance()->onLoadDataContainer('tl_theme');

        $this->assertArrayNotHasKey('label', $GLOBALS['TL_DCA']['tl_theme']['fields']['encoreEntries']);
        $this->assertSame(['Theme entries', 'Inherited by layouts.'], $GLOBALS['TL_LANG']['tl_theme']['encoreEntries']);
    }

    public function testExplicitFieldLabelSurvivesLaterLanguageLoading(): void
    {
        $options = EncoreEntriesSelectField::register('tl_custom_labels');
        $options->setFieldLabel(['Custom entries', 'Custom help']);
        $GLOBALS['TL_LANG']['tl_custom_labels']['encoreEntries'] = ['Table entries', 'Table help'];
        $this->createTestInstance()->onLoadDataContainer('tl_custom_labels');

        // A later language load replaces the table translation.
        $GLOBALS['TL_LANG']['tl_custom_labels']['encoreEntries'] = ['Later entries', 'Later help'];
        $field = $GLOBALS['TL_DCA']['tl_custom_labels']['fields']['encoreEntries'];
        $this->assertSame(['Custom entries', 'Custom help'], $field['label'] ?? $GLOBALS['TL_LANG']['tl_custom_labels']['encoreEntries']);
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
