<?php

    namespace AloFramework\Cache;

    /**
     * Cache-related exceptions
     * @author Art <a.molcanovas@gmail.com>
     */
    class CacheException extends \Exception {

        /**
         * Code when a required extension is not installed or enabled
         * @var int
         */
        const E_EXTENSION_DISABLED = 1;
    }
