<?php

namespace HeimrichHannot\EncoreBundle\Test\Dca;

use Contao\LayoutModel;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\EventListener\DcaField\EncoreEntriesSelectFieldListener;
use Symfony\Component\Translation\Translator;

class ThemeEncoreTest extends ContaoTestCase
{
    public function testThemeAndLayoutFields(): void
    {
        $root = dirname(__DIR__, 2);
        $core = dirname((new \ReflectionClass(LayoutModel::class))->getFileName(), 2);
        require_once $core . '/dca/tl_theme.php';
        require_once $core . '/dca/tl_layout.php';
        require $root . '/contao/dca/tl_theme.php';
        require $root . '/contao/dca/tl_layout.php';
        $listener = new EncoreEntriesSelectFieldListener(new Translator('en'));
        $listener->onLoadDataContainer('tl_theme');
        $listener->onLoadDataContainer('tl_layout');

        $theme = $GLOBALS['TL_DCA']['tl_theme'];
        $layout = $GLOBALS['TL_DCA']['tl_layout'];
        $this->assertStringContainsString('addEncore,encoreEntries', $theme['palettes']['default']);
        $this->assertArrayNotHasKey('active', $theme['fields']['encoreEntries']['eval']['multiColumnEditor']['fields']);
        $this->assertArrayHasKey('active', $layout['fields']['encoreEntries']['eval']['multiColumnEditor']['fields']);
        $this->assertSame('i', $layout['fields']['addEncore']['default']);
        $this->assertSame(['i', '1', '0'], $layout['fields']['addEncore']['options']);
        $this->assertArrayNotHasKey('addEncore', $layout['subpalettes']);
        $this->assertStringContainsString('addEncore,encoreEntries', $layout['palettes']['default']);
        $this->assertArrayNotHasKey('label', $theme['fields']['addEncore']);
        foreach (['de', 'en'] as $locale) {
            require $root . '/contao/languages/' . $locale . '/tl_theme.php';
            require $root . '/contao/languages/' . $locale . '/tl_layout.php';
            $this->assertNotEmpty($GLOBALS['TL_LANG']['tl_theme']['addEncore'][0]);
            $this->assertCount(3, $GLOBALS['TL_LANG']['tl_layout']['encoreActivationOptions']);
        }
    }
}
