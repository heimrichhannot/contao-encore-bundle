<?php

namespace HeimrichHannot\EncoreBundle\Test\Dca;

use Contao\LayoutModel;
use Contao\DC_Table;
use Contao\System;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\DataContainer\PaletteBuilder;
use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\DBAL\DriverManager;
use Symfony\Bundle\SecurityBundle\Security;
use HeimrichHannot\EncoreBundle\EventListener\DataContainer\Layout\ConfigOnPaletteListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
        $this->assertStringNotContainsString('encoreEntries', $theme['palettes']['default']);
        $this->assertSame('encoreEntries', $theme['subpalettes']['addEncore']);
        $this->assertTrue($theme['fields']['addEncore']['eval']['submitOnChange']);
        $this->assertArrayNotHasKey('active', $theme['fields']['encoreEntries']['eval']['multiColumnEditor']['fields']);
        $this->assertArrayHasKey('active', $layout['fields']['encoreEntries']['eval']['multiColumnEditor']['fields']);
        $this->assertSame('i', $layout['fields']['addEncore']['default']);
        $this->assertSame(['i', '1', '0'], $layout['fields']['addEncore']['options']);
        $this->assertArrayNotHasKey('addEncore', $layout['subpalettes']);
        $this->assertStringNotContainsString('encoreEntries', $layout['palettes']['default']);
        $this->assertStringContainsString('encoreEntries', $layout['subpalettes']['addEncore_1']);
        $this->assertTrue($layout['fields']['addEncore']['eval']['submitOnChange']);
        $this->assertArrayNotHasKey('label', $theme['fields']['addEncore']);
        foreach (['de', 'en'] as $locale) {
            require $root . '/contao/languages/' . $locale . '/tl_theme.php';
            require $root . '/contao/languages/' . $locale . '/tl_layout.php';
            $this->assertNotEmpty($GLOBALS['TL_LANG']['tl_theme']['addEncore'][0]);
            $this->assertCount(3, $GLOBALS['TL_LANG']['tl_layout']['encoreActivationOptions']);
        }
    }
    /** @dataProvider visibilityProvider */
    public function testNativePaletteVisibility(string $table, string $mode, ?string $themeEnabled, ?string $posted, bool $bulk, bool $expected): void
    {
        $previousDca = $GLOBALS['TL_DCA'];
        $containerProperty = new \ReflectionProperty(System::class, 'objContainer');
        $previousContainer = $containerProperty->getValue();
        $root = dirname(__DIR__, 2);
        $request = new Request($bulk ? ['act' => 'editAll'] : [], null === $posted ? [] : [
            'FORM_SUBMIT' => $table,
            $bulk ? 'addEncore_7' : 'addEncore' => $posted,
        ]);
        $stack = new RequestStack();
        $stack->push($request);
        $container = new ContainerBuilder();
        $container->set('request_stack', $stack);
        $scope = $this->createMock(ScopeMatcher::class);
        $scope->method('isBackendRequest')->willReturn(true);
        $container->set('contao.routing.scope_matcher', $scope);
        if (class_exists(PaletteBuilder::class)) {
            $framework = (new \ReflectionClass(ContaoFramework::class))->newInstanceWithoutConstructor();
            $container->set('contao.data_container.palette_builder', new PaletteBuilder(
                $framework, $stack, new Security($container),
                DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]),
            ));
        }
        System::setContainer($container);

        try {
            // Minimal host palette; the real extension DCA supplies all Encore definitions.
            $GLOBALS['TL_DCA'][$table] = ['palettes' => ['default' => '{config_legend},name;{modules_legend},modules'], 'fields' => [], 'subpalettes' => []];
            require $root . '/contao/dca/' . $table . '.php';
            if ('tl_layout' === $table) {
                $GLOBALS['TL_DCA'][$table]['config']['onpalette_callback'] = [new ConfigOnPaletteListener($stack)];
            }
            $dc = $this->getMockBuilder(DC_Table::class)->disableOriginalConstructor()->onlyMethods(['getCurrentRecord'])->getMock();
            (new \ReflectionProperty(DC_Table::class, 'strTable'))->setValue($dc, $table);
            $dc->id = 7;
            $row = ['id' => 7, 'pid' => 2, 'addEncore' => $mode];
            $dc->method('getCurrentRecord')->willReturnCallback(static function ($id = null, $lookupTable = null) use ($row, $themeEnabled, $table) {
                return 'tl_theme' === $lookupTable && 'tl_theme' !== $table ? (null === $themeEnabled ? null : ['addEncore' => $themeEnabled]) : $row;
            });

            $palette = $dc->getPalette();
            $this->assertSame($expected, str_contains($palette, 'encoreEntries'), $palette);
            $this->assertStringContainsString('addEncore', $palette);
            if ('tl_layout' === $table) {
                $this->assertSame($expected, str_contains($palette, 'encoreStylesheetsImportsTemplate'), $palette);
                $this->assertSame($expected, str_contains($palette, 'encoreScriptsImportsTemplate'), $palette);
            }
        } finally {
            $GLOBALS['TL_DCA'] = $previousDca;
            $containerProperty->setValue(null, $previousContainer);
        }
    }

    public function visibilityProvider(): array
    {
        return [
            ['tl_theme', '', null, null, false, false],
            ['tl_theme', '1', null, null, false, true],
            ['tl_theme', '', null, '1', false, true],
            ['tl_theme', '1', null, '', false, false],
            ['tl_layout', 'i', '1', null, false, true],
            ['tl_layout', 'i', '', null, false, false],
            ['tl_layout', 'i', null, null, false, false],
            ['tl_layout', '1', '', null, false, true],
            ['tl_layout', '0', '1', null, false, false],
            ['tl_layout', '0', '', '1', false, true],
            ['tl_layout', '1', '1', 'i', false, true],
            ['tl_layout', 'i', '1', '0', false, false],
            ['tl_layout', '0', '1', 'i', true, true],
            ['tl_layout', '1', '', 'i', true, false],
        ];
    }

}
