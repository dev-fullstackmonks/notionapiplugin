<?php

namespace LWM\Disc\Shortcodes;

use LWM\Disc\Core\Registrable;

if (!class_exists('LWM\Disc\Shortcodes\Shortcode')) {
    /**
     * Class Shortcode
     * @package LWM\Disc\Shortcodes
     */
    abstract class Shortcode extends Registrable implements ShortcodeInterface
    {
        /**
         *
         */
        public function __invoke()
        {
            $this->register();
        }

        /**
         * @return string
         */
        abstract public function getShortcodeTag();

        /**
         *
         */
        public function register()
        {
            add_shortcode($this->getShortcodeTag(), [$this, 'init']);
        }

        /**
         * @param $atts
         * @param null $shortcode_content
         */
        public function init($atts, $shortcode_content = null)
        {
            echo $this->render($atts);
        }

        /**
         * @param array $atts
         * @param null $view_file
         *
         * @return false|string|void
         */
        public function render($atts = [], $view_file = null)
        {
            $view_file = $view_file ?: 'view.php';
            extract($atts);
            ob_start();
            $base_class     = basename(strtr(static::class, ['\\' => '/']));
            $path_view_file = __DIR__ . "/{$base_class}/views/{$view_file}";
            if (file_exists($path_view_file)) {
                include $path_view_file;
            } elseif (file_exists(__DIR__ . '/common/views/view.php')) {
                include __DIR__ . '/common/views/view.php';
            }

            return ob_get_clean();
        }
    }
}
