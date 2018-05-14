<?php

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['generatePage']['huh.encore-bundle']       = ['huh.encore.listener.hooks', 'addEncore'];
$GLOBALS['TL_HOOKS']['loadDataContainer']['huh.encore-bundle']  = ['huh.encore.listener.hooks', 'cleanGlobalArrays'];