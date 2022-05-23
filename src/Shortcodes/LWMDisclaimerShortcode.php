<?php

namespace LWM\Disc\Shortcodes;

use LWM\Disc\Utilities\Helpers;
use LWM\Disc\Utilities\NumberToWords;

use function LWM\Disc\lwmDisc;

if (!class_exists('LWM\Disc\Shortcodes\LWMDisclaimerShortcode')) {
    /**
     * Class LWMDisclaimerShortcode
     * @package LWM\Disc\Shortcodes
     */
    abstract class LWMDisclaimerShortcode extends Shortcode
    {
        /**
         * @var string
         */
        protected $json;

        /**
         * These are set from external json
         *
         * @var string[]
         */
        protected $jsonTickerKeys = [
            'usTicker',
            'canadianTicker',
            'germanTicker',
            'londonTicker',
        ];

        /**
         * @var string[]
         */
        protected $statuses = [
            'pending',
            'tbd',
            'on hold',
        ];

        /**
         * @param $data
         *
         * @return string
         */
        protected function getOutputString($data, $hasParagraph = true)
        {
            $start             = $data['start'] ?? '';
            $end               = $data['end'] ?? '';
            $previous_data     = $data['previous_data'] ?? '';
            $hired_person      = $data['hired_person'] ?? ' has been hired ';
            $paying_party      = $data['paying_party'] ?? '';
            $start_date        = $data['start_date'] ?? '';
            $end_date          = $data['end_date'] ?? '';
            $information_about = $data['information_about'] ?? '';

            $output = sprintf(
                '<p>%sPursuant to an agreement between TD Media LLC and %s' .
                ', TD Media LLC %sfor a period beginning on ' .
                '%s and ending on %s ' .
                'to publicly disseminate information about ' .
                '%s via digital communications.%s</p>',
                $start,
                $paying_party,
                $hired_person,
                $start_date,
                $end_date,
                $information_about,
                $end,
            );

            // make sure we have single dot
            $output = preg_replace('/\.\./m', '.', $output);
            // remove double spaces
            $output = preg_replace('/\s{2,}/m', ' ', $output);

            $output = $this->capitalizeFirstLetter($output);


            if (!$hasParagraph) {
                return strip_tags($output);
            }

            return $output;
        }

        /**
         * @param $payment
         * @param $total
         * @param $checkPayExpected
         *
         * @return string
         */
        protected function getContentNumberToWords($payment, $total, $checkPayExpected)
        {
            $price_in_words = ' We have not been paid.';
            if ($this->hasPayment($payment)) {
                $start  = 'we have been paid an additional';
                $end    = 'via bank wire transfer.';
                $amount = $this->getPaymentString($payment);

                if ('Yes' === $checkPayExpected) {
                    $start = 'we expect to be paid';
                    $end   = '';
                } else {
                    $start = 'we have been paid';
                }

                if (0 !== $total) {
                    $start .= ' an additional';
                }


                // make sure it doesnt have space in the end
                $price_in_words = rtrim(
                    sprintf(
                        ' %s %s USD %s',
                        $start,
                        $amount,
                        $end
                    )
                );
            }

            return $price_in_words;
        }

        /**
         * Capitalize first letter of the first word
         * after dot and space
         *
         * @param $string
         *
         * @return string|string[]|null
         */
        private function capitalizeFirstLetter($string)
        {
            return preg_replace_callback(
            /*
             * \. matches the character . literally (case sensitive)
             * \s matches any whitespace character (equivalent to [\r\n\t\f\v ])
             * * matches the previous token between zero and unlimited times
             * \K resets the starting point of the reported match.
             * \w matches any word character (equivalent to [a-zA-Z0-9_])
             */
                '/\.\s*\K\w/',
                function ($word) {
                    $first_letter = $word[0];

                    return strtoupper($first_letter);
                },
                $string
            );
        }

        /**
         * @param $status
         *
         * @return bool
         */
        protected function hasStatus($status)
        {
            return !empty(
            array_filter(
                $this->statuses,
                function ($item) use ($status) {
                    return false !== strpos(strtolower($status), $item);
                }
            )
            );
        }

        /**
         * @param $record
         *
         * @return mixed
         */
        protected function getEndDate($record)
        {
            $end_date = $record->dateRange->endDate;
            if (empty(trim($record->dateRange->endDate))) {
                $end_date = $record->dateRange->startDate;
            }

            return $end_date;
        }

        /**
         * @param $payment
         *
         * @return string
         */
        protected function getPaymentString($payment)
        {
            // dollars and cents need to be separated
            $notion_price = explode('.', $payment);
            $dollars      = new NumberToWords($payment);
            $output       = "{$dollars} dollars";
            if (count($notion_price) > 1) {
                $dollars = new NumberToWords($notion_price[0]);
                $cents   = new NumberToWords($notion_price[1]);
                $output  = "{$dollars} and {$cents} cents";
            }

            return $output;
        }


        /**
         * @param $payment
         *
         * @return bool
         */
        protected function hasPayment($payment)
        {
            return !empty($payment) && 0 !== (int)$payment;
        }

        /**
         * @param $typeRecord
         * @param $payingPartyKey
         *
         * @return string
         */
        protected function getPayingPartySecondKey($typeRecord, $payingPartyKey)
        {
            return strtolower(
                str_replace(' ', '', $typeRecord->payingParty) . $payingPartyKey
            );
        }

        /**
         * @param $typeRecord
         *
         * @return string
         */
        protected function getPayingPartyKey($typeRecord)
        {
            return implode(
                '',
                array_map(
                    function ($jsonTickerKey) use ($typeRecord) {
                        if (isset($typeRecord->clientTickerDb->{$jsonTickerKey})) {
                            return Helpers::lowerTrim($typeRecord->clientTickerDb->{$jsonTickerKey});
                        }

                        return '';
                    },
                    $this->jsonTickerKeys
                )
            );
        }

        /**
         * @return \string[][]
         */
        protected function getTitlesTypes()
        {
            /* Here we have created array for each type of notion data */
            return [
                'influencer'          => [
                    'INFLUENCER COMPENSATION',
                    'Influencer',
                    'Influencer',
                    '.',
                ],
                'interview'           => [
                    'INTERVIEW COMPENSATION',
                    'Interviewer',
                    'Interviewer',
                    '.',
                ],
                'blogger'             => [
                    'BLOGGER COMPENSATION',
                    'Blogger',
                    'Blogger',
                    '.',
                ],
                'social media outlet' => [
                    'SOCIAL MEDIA OUTLET COMPENSATION',
                    'Social Media Outlet',
                    'Social Media Outlet',
                    '.',
                ],
                'podcaster'           => [
                    'PODCASTER COMPENSATION',
                    'Podcaster',
                    'Podcaster',
                    '.',
                ],
                'publication'         => [
                    'PUBLICATION COMPENSATION',
                    'Publication',
                    'Publication',
                    ' to have articles published on Seeking Alpha and other various online publications which we will disclose as the articles are published.',
                ],
                'radio'               => [
                    'RADIO COMPENSATION',
                    'a Radio broadcast agency',
                    'a Radio broadcast agency',
                    '.',
                ],
                'tv'                  => [
                    'TV COMPENSATION',
                    'a TV broadcast agency',
                    'a TV broadcast agency',
                    '.',
                ],
            ];
        }

        /**
         * This will create a ticker:country code output
         *
         * An example
         * (JNCCF:US) (JNC:CA)
         *
         * @param $row
         *
         * @return string
         */
        protected function getContentTicker($row)
        {
            $content_ticker = '';

            if (!empty($row->clientTickerDb)) {
                if (!empty($row->clientTickerDb->usTicker)) {
                    $content_ticker .= ' (' . $row->clientTickerDb->usTicker . ':US)';
                }
                if (!empty($row->clientTickerDb->canadianTicker)) {
                    $content_ticker .= ' (' . $row->clientTickerDb->canadianTicker . ':CA)';
                }
                if (!empty($row->clientTickerDb->germanTicker)) {
                    $content_ticker .= ' (' . $row->clientTickerDb->germanTicker . ':DE)';
                }
                if (!empty($row->clientTickerDb->londonTicker)) {
                    $content_ticker .= ' (' . $row->clientTickerDb->londonTicker . ':LA)';
                }
            }

            return $content_ticker;
        }


        /**
         * @param null $jsonKey
         *
         * @return false|mixed|string
         */
        protected function getJsonData($jsonKey = 'active-clients-calendar')
        {
            $json = '';
            $key  = $jsonKey ?: $this->jsonFile;
            try {
                $json = get_network_option(null, $key, '{}');
                if (!empty($json)) {
                    $json = json_decode($json);
                }
            } catch (\ReflectionException $e) {
            }

            return $json;
        }
    }
}
