<?php

namespace LWM\Disc\Services;

use LWM\Disc\Core\Registrable;
use LWM\Disc\Utilities\Helpers;
use LWM\Disc\Utilities\Mailer;
use WP_Error;

if (!class_exists('LWM\Disc\Services\API')) {
    /**
     * Class API
     *
     * @package LWM\Disc\Services
     */
    class API extends Registrable
    {

        /**
         * @var string
         */
        private const CRON_ID = 'update_notion';
        /**
         * @var string
         */
        private const CRON_HOOK = 'lwm_trigger_notion_update';
        /**
         * @var string
         */
        private const CRON_SCHEDULE = 'hourly';
        /**
         * @var string
         */
        private const API_KEY = '0WnlIHzZNKYuin0IBOJV';

        /**
         * @var string
         */
        private const API_URL = 'api.lifewatermedia.com';

        /**
         * @var string[]
         */
        private $jsonKeys = [
            'active-clients-calendar',
            'media-booking',
        ];

        /**
         * @return string[]
         */
        public function getJsonKeys(): array
        {
            return $this->jsonKeys;
        }

        /**
         * @var string
         */
        private $directory = 'data';

        /**
         *
         */
        public function updateNotion()
        {
            foreach ($this->jsonKeys as $json_key) {
                $this->getRemote($json_key);
            }
        }

        /**
         *
         */
        public function register()
        {
            // register & run cron only for main site
            if (is_main_site()) {
                add_action(self::CRON_HOOK, [$this, 'updateNotion']);
                $this->scheduleEvent();
            }
        }

        /**
         * Get full path of json directory
         *
         * @return string
         */
        public function getDirectory(): string
        {
            // set full path if not previously set
            if (!preg_match('#(' . LWMDISC_PLUGIN_DIR . ')#', $this->directory)) {
                $this->directory = LWMDISC_PLUGIN_DIR . "/{$this->directory}";
            }

            return $this->directory;
        }

        /**
         * Schedule event.
         */
        private function scheduleEvent()
        {
            if (!wp_next_scheduled(self::CRON_HOOK)) {
                wp_schedule_event(
                    time(),
                    self::CRON_SCHEDULE,
                    self::CRON_HOOK
                );
            }
        }

        /**
         * @param $remote
         *
         * @return bool|WP_Error
         */
        private function isRemote($remote)
        {
            if (is_wp_error($remote)) {
                return new WP_Error(
                    'site_down',
                    esc_html__(
                        'Unable to communicate with Instagram.',
                        'lwm-disclaimer'
                    )
                );
            }
            if (200 !== wp_remote_retrieve_response_code($remote)) {
                return new WP_Error(
                    'invalid_response',
                    esc_html__(
                        'API did not return a 200.',
                        'lwm-disclaimer'
                    )
                );
            }

            return true;
        }


        /**
         * @param $remote
         * @param string $key
         */
        private function saveJson($remote, $key = 'active-clients-calendar')
        {
            $json = wp_remote_retrieve_body($remote);
            if (Helpers::hasValue($json)) {
                update_network_option(null, $key, $json);
            }
        }

        /**
         * @param string $api
         */
        private function getRemote($api = 'active-clients-calendar')
        {
            $api_url = self::API_URL;
            $remote  = wp_remote_get(
                "https://{$api_url}/{$api}/",
                [
                    'headers' => [
                        'x-api-key'    => self::API_KEY,
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            if ($error = $this->isRemote($remote)) {
                $this->saveJson($remote, $api);
            } else {
                $mailer = new Mailer();
                $mailer->setSubject("Notion API({$api}) status: NOT WORKING - lifewatermedia");
                $mailer->setMessage(
                    'Hello admin,<br/> Please check api code because data is not coming through API.<br/>Thanks!!'
                );
                $mailer->sendEmail($api);
            }
        }
    }
}
