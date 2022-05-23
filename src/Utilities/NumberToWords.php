<?php

namespace LWM\Disc\Utilities;

if (!class_exists('LWM\Disc\Utilities\NumberToWords')) {
    /**
     * This will convert numbers to words
     *
     * Class NumberToWords
     *
     * @package LWM\Disc\Utilities
     */
    class NumberToWords
    {
        private $dictionary = [
            0                   => 'Zero',
            1                   => 'One',
            2                   => 'Two',
            3                   => 'Three',
            4                   => 'Four',
            5                   => 'Five',
            6                   => 'Six',
            7                   => 'Seven',
            8                   => 'Eight',
            9                   => 'Nine',
            10                  => 'Ten',
            11                  => 'Eleven',
            12                  => 'Twelve',
            13                  => 'Thirteen',
            14                  => 'Fourteen',
            15                  => 'Fifteen',
            16                  => 'Sixteen',
            17                  => 'Seventeen',
            18                  => 'Eighteen',
            19                  => 'Nineteen',
            20                  => 'Twenty',
            30                  => 'Thirty',
            40                  => 'Forty',
            50                  => 'Fifty',
            60                  => 'Sixty',
            70                  => 'Seventy',
            80                  => 'Eighty',
            90                  => 'Ninety',
            100                 => 'Hundred',
            1000                => 'Thousand',
            1000000             => 'Million',
            1000000000          => 'Billion',
            1000000000000       => 'Trillion',
            1000000000000000    => 'Quadrillion',
            1000000000000000000 => 'Quintillion',
        ];
        /**
         * @var int
         */
        private $number;

        /**
         * NumberToWords constructor.
         */
        public function __construct(int $number)
        {
            $this->number = $number;
        }

        public function __toString()
        {
            return $this->numberToWords($this->number);
        }


        /**
         * Below function is convert number to words
         *
         * @param $number
         *
         * @return bool|string
         */
        public function numberToWords($number)
        {
            $hyphen      = ' ';
            $conjunction = '  ';
            $separator   = ' ';
            $negative    = 'negative ';
            $decimal     = ' point ';

            if (!is_numeric($number)) {
                return false;
            }

            if ($this->isNumberBetweenAccepted($number)) {
                trigger_error(
                    'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                    E_USER_WARNING
                );

                return false;
            }

            if ($number < 0) {
                return $negative . $this->numberToWords(abs($number));
            }

            $string = $fraction = null;

            if (false !== strpos($number, '.')) {
                [$number, $fraction] = explode('.', $number);
            }

            if ($number < 21) {
                $string = $this->dictionary[$number];
            } elseif ($number < 100) {
                $tens   = ((int)($number / 10)) * 10;
                $units  = $number % 10;
                $string = $this->dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $this->dictionary[$units];
                }
            } elseif ($number < 1000) {
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string    = $this->dictionary[$hundreds] . ' ' . $this->dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->numberToWords($remainder);
                }
            } else {
                $base_unit         = pow(1000, floor(log($number, 1000)));
                $number_base_units = (int)($number / $base_unit);
                $remainder         = $number % $base_unit;
                $string            = $this->numberToWords($number_base_units) . ' ' . $this->dictionary[$base_unit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->numberToWords($remainder);
                }
            }

            if (null !== $fraction && is_numeric($fraction)) {
                $string .= $decimal;
                $words  = [];
                foreach (str_split((string)$fraction) as $number) {
                    $words[] = $this->dictionary[$number];
                }
                $string .= implode(' ', $words);
            }

            return strtolower($string);
        }

        private function isNumberBetweenAccepted($number)
        {
            return ($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX;
        }
    }
}
