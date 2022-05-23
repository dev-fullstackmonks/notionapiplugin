<?php

namespace LWM\Disc\Core;

if (!class_exists('LWM\Disc\Core\Registrable')) {
    /**
     * Class Registrable
     * @package LWM\Disc\Core
     */
    abstract class Registrable implements InterfaceRegistrable
    {
        /**
         * Registrable constructor.
         */
        public function __construct()
        {
            $this->register();
        }
    }
}
