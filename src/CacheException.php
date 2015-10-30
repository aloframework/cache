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

        /**
         * Code when the client is not set in CacheItem
         * @var int
         */
        const E_CLIENT_NOT_SET = 2;

        /**
         * Code when no key is set
         * @var int
         */
        const E_NO_KEY = 3;
    }
