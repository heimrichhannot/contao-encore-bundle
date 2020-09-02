<?php

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['generatePage']['huh.encore-bundle']      = [\HeimrichHannot\EncoreBundle\EventListener\GeneratePageListener::class, '__invoke'];
$GLOBALS['TL_HOOKS']['replaceDynamicScriptTags']['huh_encore'] = [\HeimrichHannot\EncoreBundle\EventListener\ReplaceDynamicScriptTagsListener::class, '__invoke'];