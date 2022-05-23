<?php

namespace LWM\Disc\Core;

if (!interface_exists('LWM\Disc\Core\InterfaceRegistrable')) {
    /**
     * Interface InterfaceRegistrable
     * @package LWM\Disc\Core
     */
    interface InterfaceRegistrable
    {
        /**
         * @return mixed
         */
        public function register();
    }
}
