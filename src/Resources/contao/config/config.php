<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\EncoreBundle\EventListener\Contao\GetPageLayoutListener;
use HeimrichHannot\EncoreBundle\EventListener\GeneratePageListener;
use HeimrichHannot\EncoreBundle\EventListener\ReplaceDynamicScriptTagsListener;

$GLOBALS['TL_HOOKS']['generatePage']['huh.encore-bundle'] = [GeneratePageListener::class, '__invoke'];
$GLOBALS['TL_HOOKS']['getPageLayout']['huh_encore'] = [GetPageLayoutListener::class, '__invoke'];
$GLOBALS['TL_HOOKS']['replaceDynamicScriptTags']['huh_encore'] = [ReplaceDynamicScriptTagsListener::class, '__invoke'];
