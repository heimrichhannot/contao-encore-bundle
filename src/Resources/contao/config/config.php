<?php

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['generatePage']['huh.encore-bundle']       = [\HeimrichHannot\EncoreBundle\EventListener\GeneratePageListener::class, 'onGeneratePage'];