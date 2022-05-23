<?php

namespace LWM\Disc\Shortcodes\ClientCalendar;

use Exception;
use LWM\Disc\Shortcodes\LWMDisclaimerShortcode;
use LWM\Disc\Utilities\Helpers;

if (!class_exists('LWM\Disc\Shortcodes\ClientCalendar\ClientCalendar')) {
    /**
     * Class ClientCalendar
     * @package LWM\Disc\Shortcodes\ClientCalendar
     */
    class ClientCalendar extends LWMDisclaimerShortcode
    {

        /**
         * @var string
         */
        protected $jsonFile = 'active-clients-calendar';

        /**
         * @var array
         */
        private $manageWholeData;

        /**
         * @var
         */
        private $ticker;

        /**
         * @return string
         */
        public function getShortcodeTag()
        {
            return 'getNotionClientCalendarData';
        }

        /**
         * @param $atts
         * @param null $shortcode_content
         */
        public function init($atts, $shortcode_content = null)
        {
            $this->json = $this->getJsonData();
            $content    = false;

            if (!empty($this->json)) {
                $shortcodes = extract(
                    shortcode_atts(
                        [
                            'ticker' => '',
                        ],
                        $atts
                    )
                );

                $this->ticker = $ticker;
                // this will be used in the following methods
                $this->setWholeData();

                ksort($this->manageWholeData);

                /* Sort notion data type alphabetically*/
                $calculated_data = [];
                $previous_data   = [];
                if ($this->manageWholeData) {
                    foreach ($this->manageWholeData as $key => $date_record) {
                        foreach ($this->manageWholeData[$key] as $key => $type_record) {
                            $output_string = [
                                'end_date'     => Helpers::getDate($this->getEndDate($type_record)),
                                'start_date'   => Helpers::getDate($type_record->dateRange->startDate),
                                'paying_party' => $type_record->payingParty,
                            ];

                            $ticker_code             = $this->getContentTicker($type_record);
                            $paying_party_key        = $this->getPayingPartyKey($type_record);
                            $paying_party_second_key = $this->getPayingPartySecondKey($type_record, $paying_party_key);

                            $total = 0;
                            if (isset($calculated_data[$paying_party_key])) {
                                $calculated_data[$paying_party_key] = $calculated_data[$paying_party_key] + $type_record->payment;
                                $total                              = $calculated_data[$paying_party_key];
                            } else {
                                $calculated_data[$paying_party_key] = $type_record->payment;
                            }

                            if (!empty($type_record->otherName)) {
                                $number_to_words                    = $this->getContentNumberToWords(
                                    $type_record->payment,
                                    $total,
                                    $type_record->payExpected
                                );
                                $output_string['information_about'] = "({$type_record->otherName})";
                                $output_string['end']               = ". We own zero shares of {$output_string['information_about']} {$number_to_words} to disseminate information about {$output_string['information_about']} via digital communications.";
                            } elseif (0 === $total) {
                                $output_string['end']                                       = $this->getContentNumberToWords(
                                    $type_record->payment,
                                    $total,
                                    $type_record->payExpected
                                );
                                $output_string['information_about']                         = $this->getContentTicker(
                                    $type_record
                                );
                                $previous_data[$paying_party_key][$paying_party_second_key] = $this->getOutputString(
                                    $output_string,
                                    false
                                );
                            } else {
                                $number_to_words = $this->getContentNumberToWords(
                                    $type_record->payment,
                                    $total,
                                    $type_record->payExpected
                                );

                                $output_string['end']    = $number_to_words;
                                $second_numbers_to_words = $this->getContentNumberToWords(
                                    $total,
                                    0,
                                    $type_record->payExpected
                                );
                                $start_string            = '';
                                foreach ($previous_data[$paying_party_key] as $key => $singleRowData) {
                                    if ($paying_party_second_key !== $key) {
                                        $start_string .= str_replace('additional', '', $singleRowData) . ' ';
                                    }
                                }
                                $information_about                  = $this->getContentTicker(
                                    $type_record
                                );
                                $output_string['information_about'] = $information_about;
                                $output_string['start']             = $start_string;
                                if ('No' === $type_record->payExpected) {
                                    $output_string['end'] .= ". We own zero shares of {$information_about}. To date {$second_numbers_to_words} to disseminate information about {$information_about} via digital communications.";
                                } else {
                                    $output_string['end'] .= " To date we have not been paid. We own zero shares of {$information_about}.";
                                }
                                $previous_data[$paying_party_key][$paying_party_second_key] = "Pursuant to an agreement between TD Media LLC and {$output_string['paying_party']}, TD Media LLC has been hired for a period beginning on {$output_string['start_date']} and ending on {$output_string['end_date']} to publicly disseminate information about {$information_about} via digital communications. {$number_to_words}.";
                            }

                            $content .= $this->getOutputString($output_string);
                        }
                    }
                }
            }

            $data['content'] = $content;
            echo parent::render($data);
        }

        /**
         * @param $filteredTokens
         *
         * @return string
         */
        private function getFilteredTokenString($filteredTokens)
        {
            /* Here we are assign data from API */

            $media_bookings = $this->getJsonData('media-booking');

            $return_data = '';
            if (!empty($media_bookings)) {
                $return_data = '';
                if (!empty($media_bookings)) {
                    $media_booking_dates = [];
                    $whole_data          = [];
                    $count               = 1;
                    foreach ($media_bookings as $media_booking) {
                        if (
                            !empty($media_booking->clientTickerDb)
                            && !$this->hasStatus($media_booking->influencer)
                            && array_key_exists($this->getCombinedTickerValue($media_booking), $filteredTokens)
                        ) {
                            $key                         = Helpers::convertStringToDate(
                                $media_booking->dateRange->startDate
                            );
                            $media_booking_dates[$key][] = $media_booking;
                            $count++;
                        }
                    }

                    // sort by date
                    ksort($media_booking_dates);
                    $count = 1;

                    // this is weird but working
                    foreach ($media_booking_dates as $key => $media_booking_date) {
                        foreach ($media_booking_dates[$key] as $notionRowRecord) {
                            if ('pr' !== strtolower($notionRowRecord->type)) {
                                $whole_data[strtolower($notionRowRecord->type)][] = $notionRowRecord;
                                $count++;
                            }
                        }
                    }

                    /* Here we have created array for each type of notion data */
                    $titles_types = $this->getTitlesTypes();


                    /* Sort notion data type alphabetically*/
                    ksort($whole_data);

                    foreach ($whole_data as $key => $single_record) {
                        /* Prepare heading for each section */
                        $return_data .= '<br/><p><strong>' . $titles_types[$key][0] . '</strong></p>';
                        foreach ($whole_data[$key] as $singleRowRecord) {
                            /* Here we are convert price in words */
                            $end = 'We have not compensated this ' . $titles_types[$key][1] . '';
                            if ($this->hasPayment($singleRowRecord->payment)) {
                                $payment_string = $this->getPaymentString($singleRowRecord->payment);
                                $end            = " We have paid this {$titles_types[$key][1]} {$payment_string} USD";
                            }
                            $end_date = $this->getEndDate($singleRowRecord);

                            $ticker_code = $this->getContentTicker($singleRowRecord);


                            $return_data .= $this->getOutputString(
                                [
                                    'paying_party'      => $titles_types[$key][1],
                                    'hired_person'      => "has hired {$titles_types[$key][2]} ",
                                    'start_date'        => Helpers::getDate($singleRowRecord->dateRange->startDate),
                                    'end_date'          => Helpers::getDate($end_date),
                                    'information_about' => $ticker_code,
                                    'end'               => " {$end}.",
                                ]
                            );
                        }
                    }
                }
            }
            if (empty($return_data)) {
                $response = wp_remote_get(
                    'https://lifewatermedia.com/wp-admin/admin-ajax.php?action=updateNotionDataFromAPI'
                );
            }

            return $return_data;
        }

        /**
         * @param $item
         *
         * @return string
         */
        private function getFilteredToken($item)
        {
            $end_date  = Helpers::convertStringToDate(
                $item->dateRange->startDate
            );
            $check_key = $this->getCombinedTickerValue($item);
            // add this to property
            $this->manageWholeData[$end_date][] = $item;

            return $check_key;
        }


        /**
         * @param $records
         * @param $removed_keys
         *
         * @return array
         */
        private function getFilteredTokens($records, $removed_keys)
        {
            $filtered_tokens = [];
            foreach ($records as $index_records => $record) {
                if (!array_key_exists($index_records, $removed_keys)) {
                    foreach ($record as $index_record => $item_record) {
                        foreach ($item_record as $item) {
                            if (!empty($item->clientTickerDb)) {
                                $filtered_token                   = $this->getFilteredToken($item);
                                $filtered_tokens[$filtered_token] = $filtered_token;
                            }
                        }
                    }
                }
            }

            return $filtered_tokens;
        }

        /**
         * @param $records
         *
         * @return array
         */
        private function getRemovedKeys($records)
        {
            $current_datetime = date_create(date('Y-m-d'));
            $removed_keys     = [];
            foreach ($records as $index_records => $record) {
                foreach ($record as $index_record => $item_record) {
                    krsort($records[$index_records]);
                    $latest_data = array_values($records[$index_records])[0];
                    if (!empty($end_date = $latest_data[0]->dateRange->endDate)) {
                        try {
                            $end_datetime        = date_create(date('Y-m-d', strtotime($end_date)));
                            $difference_datetime = date_diff($current_datetime, $end_datetime);
                            $difference_days     = $difference_datetime->format('%R%a');
                            if ($difference_days < -31) {
                                $removed_keys[$index_records] = $index_records;
                            }
                        } catch (Exception $e) {
                        }
                    }
                }
            }

            return $removed_keys;
        }

        /**
         * @param $record
         *
         * @return bool
         */
        private function hasTickerValue($record)
        {
            // only tickers that have value
            $values = array_filter(
                $this->jsonTickerKeys,
                function ($item) use ($record) {
                    return !empty(trim($record->clientTickerDb->{$item})) && trim($record->clientTickerDb->{$item}) != null;
                }
            );

            return !empty($values);
        }

        /**
         * @param $record
         *
         * @return string
         */
        private function getCombinedTickerValue($record)
        {
            return implode(
                '',
                array_map(
                    function ($ticker_key) use ($record) {
                        return Helpers::lowerTrim($record->clientTickerDb->{$ticker_key});
                    },
                    $this->jsonTickerKeys
                )
            );
        }

        /**
         * @param $ticker_codes
         *
         * @return array
         */
        private function getCheckedTickerCodes($ticker_codes)
        {
            $checked_ticker_codes = [];
            array_walk(
                $ticker_codes,
                function ($ticker_code) use (&$checked_ticker_codes) {
                    $exploded_ticker = explode(':', $ticker_code);
                    if (count($exploded_ticker) > 1) {
                        // this is the country code
                        if (!empty(trim($exploded_ticker[1]))) {
                            $checked_ticker_codes[][Helpers::lowerTrim(
                                $exploded_ticker[1]
                            )] = Helpers::lowerTrim($exploded_ticker[1]);
                        } else {
                            $checked_ticker_codes[] = [];
                        }
                    } elseif (!empty(trim($exploded_ticker[0]))) {
                        $checked_ticker_codes[][Helpers::lowerTrim(
                            $exploded_ticker[0]
                        )] = Helpers::lowerTrim($exploded_ticker[0]);
                    } else {
                        $checked_ticker_codes[] = [];
                    }
                }
            );

            return $checked_ticker_codes;
        }

        /**
         * @param $ticker
         * @param $record
         *
         * @return string[]
         */
        private function tickerExistInData($ticker, $record)
        {
            return array_filter(
                $this->jsonTickerKeys,
                function ($ticker_key) use ($ticker, $record) {
                    return Helpers::lowerTrim($record->clientTickerDb->{$ticker_key}) === Helpers::lowerTrim($ticker);
                }
            );
        }

        /**
         *
         */
        private function setWholeData()
        {
            $manage_whole_data = [];
            $records           = $this->json;
            if ($records) {
                $ticker_codes = explode('~', $this->ticker);
                foreach ($records as $index => $record) {
                    if (
                        !$this->hasStatus($record->name)
                        || 'Yes' === $record->payExpected
                    ) {
                        
                        $start_key = Helpers::convertStringToDate($record->dateRange->startDate);
                        if (!empty($record->clientTickerDb)) {
                            if (
                                $this->hasTickerValue($record)
                                && !empty($this->ticker)
                            ) {
                                
                                if (count($ticker_codes) > 1) {
                                    $checked_ticker_codes = $this->getCheckedTickerCodes($ticker_codes);

                                    foreach ($this->jsonTickerKeys as $index => $json_ticker_key) {
                                        if (
                                        array_key_exists(
                                            Helpers::lowerTrim($record->clientTickerDb->{$json_ticker_key}),
                                            $checked_ticker_codes[$index]
                                        )
                                        ) {
                                            $manage_whole_data[$start_key][] = $record;
                                            // prevent duplicates
                                            // exit loop once it's found
                                            continue 2;
                                        }
                                    }
                                } elseif ($this->tickerExistInData($this->ticker, $record)) {
                                    $manage_whole_data[$start_key][] = $record;
                                }
                            } elseif ('' !== trim($record->otherName)) {
                                $manage_whole_data[$start_key][] = $record;
                            }
                        }
                    }
                }
            }
            
            $this->manageWholeData = $manage_whole_data;
        }
    }
}
