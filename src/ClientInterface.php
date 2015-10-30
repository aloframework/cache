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
        function connect($ip = null, $port = null);

        /**
         * Deletes a cached item
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string|array $key Item key or array of keys
         *
         * @return ClientInterface
         */
        function delete($key);

        /**
         * Check if the key exists
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key The key
         *
         * @return bool
         */
        function exists($key);

        /**
         * Returns a cached item
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key Item key
         *
         * @return mixed The item or null if it's not found
         */
        function getKey($key);

        /**
         * Returns all the cached items as an associative array
         * @author Art <a.molcanovas@gmail.com>
         * @return array
         */
        function getAll();

        /**
         * Purges all cached items
         * @author Art <a.molcanovas@gmail.com>
         * @return bool
         */
        function purge();

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
        function setKey($key, $value, $timeout = null);

        /**
         * Returns how many seconds this key has left before expiring
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key The key
         *
         * @return int The remaining lifetime in seconds. If the key doesn't exist 0 is returned.
         */
        function getRemainingLifetime($key);
    }
