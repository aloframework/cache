<?php

    namespace AloFramework\Cache;

    use ArrayAccess;
    use Countable;
    use IteratorAggregate;
    use Serializable;

    /**
     * The AloFramework cache interface
     * @author Art <a.molcanovas@gmail.com>
     */
    interface ClientInterface extends ArrayAccess, Countable, Serializable, IteratorAggregate {

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
         * @param string $key Item key
         *
         * @return bool True if the key existed, false if it didn't
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
        public function get($key);

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
         * @param string $key   Item key
         * @param mixed  $value Item value
         *
         * @return bool
         */
        public function set($key, $value);

        /**
         * Magically gets a cached item
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key Item key
         *
         * @return mixed
         */
        public function __get($key);

        /**
         * Magically sets a cached item
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key   Item key
         * @param mixed  $value Item value
         */
        public function __set($key, $value);
    }
