<?php

namespace LWM\Disc\Services;

use LWM\Disc\Core\Registrable;
use LWM\Disc\Utilities\Helpers;

if (!class_exists('LWM\Disc\Services\Settings')) {
    /**
     * Class Settings
     * @package LWM\Disc\Services
     */
    class Settings extends Registrable
    {
        public const OPTION_KEY = 'html_guidelines_message';

        public function addFields()
        {
            $message = $this->getGuidelineOption();

            if (is_multisite()) {
                $message = $this->getNetworkWideMessage();
            } elseif (!Helpers::hasValue($message)) {
                update_option(self::OPTION_KEY, $message);
            }
            echo wp_editor($message, 'sitepublishingguidelines', ['textarea_name' => self::OPTION_KEY]);
        }


        /**
         * @return string
         */
        public function getGuidelineOption()
        {
            return html_entity_decode(get_option(self::OPTION_KEY));
        }

        public function addSettings()
        {
            register_setting('general', self::OPTION_KEY, 'esc_html');
            add_settings_section('site-guide', 'Disclaimer Content', '__return_false', 'general');
            add_settings_field(
                self::OPTION_KEY,
                'Enter disclaimer content',
                [$this, 'addFields'],
                'general',
                'site-guide'
            );
        }

        public function register()
        {
            if (is_admin()) {
                add_action('admin_init', [$this, 'addSettings']);
            }
        }

        public function getNetworkWideMessage()
        {
//            $message = $this->getGuidelineOption();
            switch_to_blog(1);
//            if (!empty($message)) {
//                update_option(self::OPTION_KEY, $message);
//            }
            $message = $this->getGuidelineOption();
            restore_current_blog();

            return $message;
        }
    }
}
