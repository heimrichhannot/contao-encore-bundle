<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
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

    /**
     * Returns a row of a multidimensional array by field value. Returns false, if no row found.
     *
     * @param string|int $key      The array key (field name)
     * @param mixed      $value
     * @param array      $haystack a multidimensional array
     *
     * @return array|false
     */
    public static function getArrayRowByFieldValue($key, $value, array $haystack)
    {
        foreach ($haystack as $row) {
            if (!\is_array($row)) {
                continue;
            }

            if (!isset($row[$key])) {
                continue;
            }

            if ($row[$key] == $value) {
                return $row;
            }
        }

        return false;
    }
}
