<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Helper;

/**
 * @deprecated
 * @codeCoverageIgnore
 */
class EntryHelper
{
    /**
     * Clean up contao global asset arrays.
     *
     * @deprecated Use GlobalContaoAsset service instead
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
