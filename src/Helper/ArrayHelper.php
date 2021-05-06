<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Helper;

class ArrayHelper
{
    /**
     * Remove duplicate values from multi-dimensional arrays based on a given key.
     *
     * @param $array
     * @param $key
     * @param bool $flip
     *
     * @return array
     */
    public static function arrayUniqueMultidimensional($array, $key, bool $flipOrder = false)
    {
        $temp_array = [];
        $i = 0;
        $key_array = [];

        if ($flipOrder) {
            $array = array_reverse($array);
        }

        foreach ($array as $val) {
            if (!\in_array($val[$key], $key_array, true)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            ++$i;
        }
        if ($flipOrder) {
            $temp_array = array_reverse($temp_array);
        }

        return $temp_array;
    }
}
