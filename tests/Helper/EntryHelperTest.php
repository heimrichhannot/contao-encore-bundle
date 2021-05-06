<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test\Helper;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\EncoreBundle\Helper\EntryHelper;

class EntryHelperTest extends ContaoTestCase
{
    const TL_JAVASCRIPT = ['assets/jquery/js/jquery.min.js|static', 'contao-a-bundle' => '', 'contao-b-bundle' => ''];
    const TL_JQUERY = ['contao-a-bundle' => '', 'contao-c-bundle' => '', 'contao-jquery-bundle' => '', 'contao-d-bundle' => '', 'contao-e-bundle' => ''];
    const TL_USER_CSS = ['contao-a-bundle' => '', 'contao-b-bundle' => '', 'contao-jquery-bundle' => ''];
    const TL_CSS = ['contao-a-bundle' => '', 'contao-c-bundle' => '', 'contao-css-bundle' => ''];

    public function testCleanGlobalArrays()
    {
        $GLOBALS['TL_JAVASCRIPT'] = self::TL_JAVASCRIPT;
        $GLOBALS['TL_JQUERY'] = self::TL_JQUERY;
        $GLOBALS['TL_USER_CSS'] = self::TL_USER_CSS;
        $GLOBALS['TL_CSS'] = self::TL_CSS;

        EntryHelper::cleanGlobalArrays([]);

        $this->assertSame(self::TL_JAVASCRIPT, $GLOBALS['TL_JAVASCRIPT']);
        $this->assertSame(self::TL_JQUERY, $GLOBALS['TL_JQUERY']);
        $this->assertSame(self::TL_USER_CSS, $GLOBALS['TL_USER_CSS']);
        $this->assertSame(self::TL_CSS, $GLOBALS['TL_CSS']);

        EntryHelper::cleanGlobalArrays([
            'unset_global_keys' => [
                'js' => ['contao-b-bundle'],
                'jquery' => ['contao-b-bundle', 'contao-a-bundle', 'contao-jquery-bundle'],
                'css' => ['contao-a-bundle', 'contao-css-bundle'],
            ],
            'unset_jquery' => false,
        ]);

        $this->assertCount(2, $GLOBALS['TL_JAVASCRIPT']);
        $this->assertCount(3, $GLOBALS['TL_JQUERY']);
        $this->assertCount(2, $GLOBALS['TL_USER_CSS']);
        $this->assertCount(1, $GLOBALS['TL_CSS']);

        $GLOBALS['TL_JAVASCRIPT'] = self::TL_JAVASCRIPT;
        $GLOBALS['TL_JQUERY'] = self::TL_JQUERY;
        $GLOBALS['TL_USER_CSS'] = self::TL_USER_CSS;
        $GLOBALS['TL_CSS'] = self::TL_CSS;

        EntryHelper::cleanGlobalArrays([
            'unset_global_keys' => [
                'js' => ['contao-b-bundle'],
                'jquery' => ['contao-b-bundle', 'contao-a-bundle', 'contao-jquery-bundle'],
                'css' => ['contao-a-bundle', 'contao-css-bundle'],
            ],
            'unset_jquery' => true,
        ]);
        $this->assertCount(1, $GLOBALS['TL_JAVASCRIPT']);
        $this->assertCount(3, $GLOBALS['TL_JQUERY']);
        $this->assertCount(2, $GLOBALS['TL_USER_CSS']);
        $this->assertCount(1, $GLOBALS['TL_CSS']);
    }
}
