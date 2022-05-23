<?php

namespace LWM\Disc\Services;

use LWM\Disc\Core\Registrable;
use LWM\Disc\Shortcodes\InfluencerCompensation\InfluencerCompensation;
use LWM\Disc\Utilities\Helpers;
use LWM\Disc\Utilities\Mailer;

use function LWM\Disc\lwmDisc;

if (!class_exists('LWM\Disc\Services\TickerDisclaimer')) {
    /**
     * Class TickerDisclaimer
     * @package LWM\Disc\Services
     */
    class TickerDisclaimer extends Registrable
    {
        /**
         * @var string[]
         */
        public $fieldSelectors = [
            'ticker_name',
            'canadian_ticker_name',
            'german_ticker_name',
            'london_ticker_name',
        ];


        /**
         * Email interval in hours
         */
        private const EMAIL_INTERVAL = 12;

        /**
         * @var
         */
        private $postId;

        /*
         * This will add disclaimer to ticker pages
         */
        /**
         * @param null $postId
         */
        public function addDisclaimer($postId = null)
        {
            $extraData = '';
            if (null !== $postId) {
                $this->postId = $postId;


                $combined_ticker = $this->getTickerCodes();

                $html = '';
                if (Helpers::hasValue($combined_ticker)) {
                    ob_start(); ?>
                    <div class="lwm-disclaimer-box">
                        <h3 class="lwm-content__heading is-disclaimer-box">
                            <strong>Legal Disclaimer</strong>
                        </h3>
                        <hr />
                        <p><?php echo lwmDisc()->settings->getNetworkWideMessage(); ?></p>
                        <?php echo do_shortcode("[getNotionInfluencerCompensationData ticker=\"{$combined_ticker}\"]"); ?>
                        <p><strong>COMPENSATION</strong></p>
                        <?php echo do_shortcode("[getNotionClientCalendarData ticker=\"{$combined_ticker}\"]"); ?>
                    </div>

                    <?php
                    $html = ob_get_clean();
                } else {
                    $this->sendEmail($combined_ticker);
                }
            }
            echo $html;
        }

        /**
         * @param $transient
         */
        private function sendEmail($transient)
        {
            $mailer = new Mailer();

            $post_url = get_permalink($this->postId);
            $message  = "Hello admin,<br/> This page ({$post_url}) doesn't have a ticker code or ticker code is wrong";

            $site_url = get_option('siteurl');
            $subject  = "Notion API Ticker Code Missing - {$site_url}";

            $mailer->setMessage($message);
            $mailer->setSubject($subject);

            $mailer->sendEmail($transient);
        }

        /**
         * @return mixed|void
         */
        public function register()
        {
            add_action('autoAddDisclaimerInTickerPages', [$this, 'addDisclaimer'], 10, 1);
        }

        /**
         * @return string
         */
        private function getTickerCodes()
        {
            $ticker_codes = array_map(
                function ($key) {
                    return get_field($key, $this->postId);
                },
                $this->fieldSelectors
            );

            return implode('~', $ticker_codes);
        }

        /**
         * @return string
         */
        private function getGuidelineMessage()
        {
            if (is_multisite()) {
                switch_to_blog(1);
                $message = $this->getGuidelineMessage();
                restore_current_blog();

                return $message;
            }

            return $this->getGuidelineMessage();
        }
    }
}
