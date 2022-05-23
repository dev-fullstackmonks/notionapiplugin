<?php


namespace LWM\Disc\Shortcodes;

if (!interface_exists('LWM\Disc\Shortcodes\ShortcodeInterface')) {
    /**
     * Interface ShortcodeInterface
     * @package LWM\Disc\Shortcodes
     */
    interface ShortcodeInterface
    {
        /**
         * @return string
         */
        public function getShortcodeTag();

        /**
         * @return void
         */
        public function render();
    }
}
