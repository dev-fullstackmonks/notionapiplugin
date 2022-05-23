<?php

namespace LWM\Disc;

use LWM\Disc\Services\API;
use LWM\Disc\Services\Settings;
use LWM\Disc\Services\TickerDisclaimer;
use LWM\Disc\Shortcodes\Shortcode;

if (!class_exists('LWM\Disc\Plugin')) {
    /**
     * Class Plugin
     * @package LWM\Disc
     */
    class Plugin
    {
        /**
         * @var Settings
         */
        public $settings;
        /**
         * @var API
         */
        public $api;

        /**
         * Plugin constructor.
         */
        public function __construct()
        {
            $this->settings = new Settings();
            $this->api      = new API();
            new TickerDisclaimer();
        }

        public function __invoke()
        {
            $shortcodes = $this->getShortcodes();
            if ($shortcodes && !empty($shortcodes)) {
                foreach ($shortcodes as $shortcode) {
                    if (is_string($shortcode)) {
                        $shortcode = new $shortcode();
                    }
                }
            }
        }


        /*
         * Get all shortcodes
         */
        private function getShortcodes()
        {
            $namespace = rtrim('\\' . __NAMESPACE__ . '\\Shortcodes', '\\');
            $glob      = $glob = __DIR__ . '/Shortcodes/**/*.php';

            return array_map(
                static function ($file) use ($namespace) {
                    $className = basename($file, '.php');

                    // shortcodes are inside of same namespace as class name
                    $shortcode   = "{$namespace}\\{$className}\\{$className}";
                    $is_shorcode = is_subclass_of($shortcode, Shortcode::class);
                    if ($is_shorcode) {
                        return $shortcode;
                    }
                },
                glob($glob)
            );
        }
    }
}
