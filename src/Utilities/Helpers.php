<?php

namespace LWM\Disc\Utilities;

use DateTime;

if (!class_exists('LWM\Disc\Utilities\Helpers')) {
    /**
     * Class Helpers
     *
     * @package LWM\Disc\Utilities
     */
    class Helpers
    {
        /**
         * @param $word
         *
         * @return string
         */
        public static function lowerTrim($word)
        {
            return strtolower(trim($word));
        }

        /**
         * @param $string
         * @param string $format
         *
         * @return false|string
         */
        public static function convertStringToDate($string, $format = 'Ymd')
        {
            return date($format, strtotime($string));
        }

        /**
         * @param $value
         *
         * @return bool
         */
        public static function hasValue($value)
        {
            return !empty(trim($value));
        }

        /**
         * @param $date
         * @param string $format
         *
         * @return false|string
         */
        public static function getDate($date, $format = 'm/d/Y')
        {
            return date_format(
                date_create($date),
                $format
            );
        }
    }
}
