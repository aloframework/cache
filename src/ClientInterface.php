<?php

    namespace AloFramework\Cache;

    use ArrayAccess;
    use Countable;
    use IteratorAggregate;

    /**
     * The AloFramework cache interface
     * @author Art <a.molcanovas@gmail.com>
     */
    interface ClientInterface extends ArrayAccess, Countable, IteratorAggregate {

        /**
         * Connect to the cache server
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $ip   Server IP
         * @param int    $port Server port
         *
         * @return bool Whether the connection succeeded
         */
        public function connect($ip = null, $port = null);

        /**
         * Deletes a cached item
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string|array $key Item key or array of keys
         *
         * @return ClientInterface
         */
        public function delete($key);

        /**
         * Returns a cached item
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key Item key
         *
         * @return mixed The item or null if it's not found
         */
        public function getKey($key);

        /**
         * Returns all the cached items as an associative array
         * @author Art <a.molcanovas@gmail.com>
         * @return array
         */
        public function getAll();

        /**
         * Purges all cached items
         * @author Art <a.molcanovas@gmail.com>
         * @return bool
         */
        public function purge();

        /**
         * Sets a cached item
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string        $key     Item key
         * @param mixed         $value   Item value
         * @param int|\DateTime $timeout Expiration time in seconds, or a DateTime object for when it's supposed to
         *                               expire
         *
         * @return bool
         */
        public function setKey($key, $value, $timeout = null);
    }
