<?php

/**
 * Plugin Name: LifeWaterMedia Disclaimers
 * Description: This will add disclaimers to ticker pages and main site's disclaimer
 * Version: 1
 * Author: Batur Kacamak
 * Author URI: https://batur.info/
 */

namespace LWM\Disc;

defined('LWMDISC_PLUGIN_DIR') ?: define('LWMDISC_PLUGIN_DIR', untrailingslashit(__DIR__));
defined('LWMDISC_PLUGIN_URL') ?: define('LWMDISC_PLUGIN_URL', untrailingslashit(plugin_dir_url(__FILE__)));

if (!defined('ABSPATH')) {
    exit;
}

add_action(
    'plugins_loaded',
    function () {
        if (
            !class_exists(Plugin::class) &&
            file_exists($autoloader = LWMDISC_PLUGIN_DIR . '/vendor/autoload.php')
        ) {
            require_once $autoloader;
        }

        add_action('after_setup_theme', new Plugin(), 100);
    }
);

if (!function_exists('lwmDisc')) {
    /**
     * Singleton
     *
     * @return Plugin
     */
    function lwmDisc()
    {
        static $plugin = null; // cache

        if (null === $plugin) {
            $plugin = new Plugin();
        }

        return $plugin;
    }
}
