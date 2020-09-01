<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\EncoreBundle\Helper;


class EntryHelper
{
    /**
     * Clean up contao global asset arrays.
     */
    public static function cleanGlobalArrays(array $bundleConfig): void
    {
        // js
        if (isset($bundleConfig['unset_global_keys']['js']) && \is_array($bundleConfig['unset_global_keys']['js'])) {
            $jsFiles = &$GLOBALS['TL_JAVASCRIPT'];

            if (\is_array($jsFiles)) {
                foreach ($bundleConfig['unset_global_keys']['js'] as $jsFile) {
                    if (isset($jsFiles[$jsFile])) {
                        unset($jsFiles[$jsFile]);
                    }
                }
            }
        }
        // jquery
        if (isset($bundleConfig['unset_global_keys']['jquery']) && \is_array($bundleConfig['unset_global_keys']['jquery'])) {
            $jqueryFiles = &$GLOBALS['TL_JQUERY'];

            if (\is_array($jqueryFiles)) {
                foreach ($bundleConfig['unset_global_keys']['jquery'] as $legacyFile) {
                    if (isset($jqueryFiles[$legacyFile])) {
                        unset($jqueryFiles[$legacyFile]);
                    }
                }
            }
        }

        // css
        if (isset($bundleConfig['unset_global_keys']['css']) && \is_array($bundleConfig['unset_global_keys']['css'])) {
            foreach (['TL_USER_CSS', 'TL_CSS'] as $arrayKey) {
                $cssFiles = &$GLOBALS[$arrayKey];

                if (\is_array($cssFiles)) {
                    foreach ($bundleConfig['unset_global_keys']['css'] as $cssFile) {
                        if (isset($cssFiles[$cssFile])) {
                            unset($cssFiles[$cssFile]);
                        }
                    }
                }
            }
        }
        if (isset($bundleConfig['unset_jquery']) && true === $bundleConfig['unset_jquery']) {
            $jsFiles = &$GLOBALS['TL_JAVASCRIPT'];
            if (false !== ($key = array_search('assets/jquery/js/jquery.min.js|static', $jsFiles, true))) {
                unset($jsFiles[$key]);
            }
        }
    }
}