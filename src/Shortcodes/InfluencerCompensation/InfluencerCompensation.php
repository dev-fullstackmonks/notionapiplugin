<?php

namespace LWM\Disc\Shortcodes\InfluencerCompensation;

use LWM\Disc\Shortcodes\LWMDisclaimerShortcode;
use LWM\Disc\Utilities\Helpers;
use LWM\Disc\Utilities\NumberToWords;

if (!class_exists('LWM\Disc\Shortcodes\InfluencerCompensation\InfluencerCompensation')) {
    /**
     * Class InfluencerCompensation
     * @package LWM\Disc\Shortcodes\InfluencerCompensation
     */
    class InfluencerCompensation extends LWMDisclaimerShortcode
    {

        /**
         * @var string
         */
        protected $jsonFile = 'media-booking';
        /**
         * @var string
         */
        private $ticker;

        /**
         * @return string
         */
        public function getShortcodeTag()
        {
            return 'getNotionInfluencerCompensationData';
        }

        /**
         * @param $payment
         * @param $title
         * @param null $checkPayExpected
         *
         * @return string
         */
        protected function getContentNumberToWords($payment, $title, $checkPayExpected = null)
        {
            $number_to_words = "We have not compensated this {$title}";
            if ($this->hasPayment($payment)) {
                $amount          = $this->getPaymentString($payment);
                $number_to_words = sprintf('We have paid this %s %s USD', $title, $amount);
            }

            return $number_to_words;
        }

        /**
         * @param mixed $atts
         * @param null|mixed $shortcode_content
         */
        public function init($atts, $shortcode_content = null)
        {
            $shortcode_atts = shortcode_atts(
                [
                    'ticker' => '',
                ],
                $atts
            );
            extract($shortcode_atts);

            $this->json = $this->getJsonData('media-booking');
            $content    = '';
            if (!empty($this->json)) {
                // set from shortcode_atts
                $this->ticker = $ticker;
                $manage_whole_data = $this->getWholeData();
                $titles_and_types  = $this->getTitlesTypes();
                foreach ($manage_whole_data as $key => $type_record) {
                    if (!isset($titles_and_types[$key][0])) {
                        continue;
                    }

                    /* Prepare heading for each section */
                    $content .= '<p><strong>' . $titles_and_types[$key][0] . '</strong></p>';
                    foreach ($manage_whole_data[$key] as $row_record) {
                        $title = $titles_and_types[$key][1];
                        /* Here we are convert price in words */
                        $number_to_words = $this->getContentNumberToWords($row_record->payment, $title);

                        // set end end date
                        $end_date = $this->getEndDate($row_record);

                        $ticker_code = $this->getContentTicker($row_record);

                        $content .= sprintf(
                            '<p>Pursuant to an agreement between TD Media LLC and %s, TD Media LLC has hired %s' .
                            ' for a period beginning on %s and ending on %s' .
                            ' to publicly disseminate information about %s' .
                            ' via digital communications%s %s.</p>',
                            $title,
                            $titles_and_types[$key][2],
                            date_format(date_create($row_record->dateRange->startDate), 'm/d/Y'),
                            date_format(date_create($end_date), 'm/d/Y'),
                            $ticker_code,
                            $titles_and_types[$key][3],
                            $number_to_words
                        );
                    }
                }
            }
            if (!$content) {
                $ajax_url = admin_url('admin-ajax.php');
                $ajax_url = add_query_arg($ajax_url, ['action' => 'updateNotionDataFromAPI']);
                $response = wp_remote_get($ajax_url);
            }

            $data['content'] = $content;
            echo parent::render($data);
        }


        /**
         * @return array
         */
        private function getTickerData()
        {
            $data    = [];
            $records = $this->json;
            $ticker = $this->ticker;
            if ($records) {
                foreach ($records as $record) {
                    if (
                        !empty($record->clientTickerDb)
                        && !$this->hasStatus($record->influencer)
                    ) {
                        if($this->hasTickerValue($record) && !empty($ticker)){
                            $ticker_codes = explode('~', $ticker);
                            if (count($ticker_codes) > 1) {
                                $check_ticker_codes = [];
                                foreach ($ticker_codes as $index => $ticker_code) {
                                    $ticker_code_index = explode(':', $ticker_code);
                                    if (count($ticker_code_index) > 1) {
                                        if (!empty(trim($ticker_code_index[1]))) {
                                            $check_ticker_codes[][Helpers::lowerTrim(
                                                $ticker_code_index[1]
                                            )] = Helpers::lowerTrim($ticker_code_index[1]);
                                        } else {
                                            $check_ticker_codes[] = [];
                                        }
                                    } elseif (!empty(trim($ticker_code_index[0]))) {
                                        $check_ticker_codes[][Helpers::lowerTrim(
                                            $ticker_code_index[0]
                                        )] = Helpers::lowerTrim($ticker_code_index[0]);
                                    } else {
                                        $check_ticker_codes[] = [];
                                    }
                                }

                                if ($this->hasTickerCodeAsKey($record, $check_ticker_codes)) {
                                    $index = Helpers::convertStringToDate($record->dateRange->startDate);

                                    $data[$index][] = $record;
                                }
                            } elseif ($this->hasTickerInJson($record, $ticker)) {
                                $index = Helpers::convertStringToDate($record->dateRange->startDate);

                                $data[$index][] = $record;
                            }
                        } else {
                            $index          = Helpers::convertStringToDate($record->dateRange->startDate);
                            $data[$index][] = $record;
                        }
                    }
                }
            }

            /* Below we are sort the data date assending */
            ksort($data);

            return $data;
        }

        /**
         * @return array
         */
        private function getWholeData()
        {
            $manage_whole_data = [];

            $manage_whole_data_date_sort = $this->getTickerData();

            foreach ($manage_whole_data_date_sort as $index => $notion_record) {
                foreach ($manage_whole_data_date_sort[$index] as $notionRowRecord) {
                    if ('pr' !== strtolower($notionRowRecord->type)) {
                        $manage_whole_data[strtolower($notionRowRecord->type)][] = $notionRowRecord;
                    }
                }
            }

            /* Sort notion data type alphabetically*/
            ksort($manage_whole_data);

            return $manage_whole_data;
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
         * @param $notion_record
         * @param array $ticker_codes
         *
         * @return bool
         */
        private function hasTickerCodeAsKey($notion_record, $ticker_codes = [])
        {
            // array_filter doesn't have index
            // start index from -1 to match 0 index at the first iteration
            $index = -1;
            return !empty(
                array_filter(
                    $this->jsonTickerKeys,
                    function ($item) use ($notion_record, $ticker_codes, &$index) {
                        $index++;
                        return array_key_exists(
                            Helpers::lowerTrim($notion_record->clientTickerDb->{$item}),
                            $ticker_codes[$index]
                        );
                    }
                )
            );
        }

        /**
         * @param $notion_record
         * @param $ticker
         *
         * @return bool
         */
        private function hasTickerInJson($notion_record, $ticker)
        {
            return !empty(
                array_filter(
                    $this->jsonTickerKeys,
                    function ($item, $index) use ($notion_record, $ticker) {
                        return Helpers::lowerTrim($notion_record->clientTickerDb->{$item}) === Helpers::lowerTrim($ticker);
                    }
                )
            );
        }
    }
}
