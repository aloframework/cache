<?php

    namespace AloFramework\Cache;

    use AloFramework\Cache\CacheException as Cex;
    use AloFramework\Cache\ClientInterface as CI;
    use DateTime;

    /**
     * A representation of a cached item
     * @author Art <a.molcanovas@gmail.com>
     */
    class CacheItem {

        /**
         * The cache client
         * @var ClientInterface
         */
        protected $client;

        /**
         * Cache item key
         * @var string
         */
        private $key;

        /**
         * The cached value
         * @var mixed
         */
        private $value;

        /**
         * The lifetime of the key in seconds
         * @var int
         */
        private $lifetime = 0;

        /**
         * Constructor
         *
         * @param string $key    Immediately set the key
         * @param mixed  $value  Immediately set the value
         * @param CI     $client Store a reference to the client
         */
        function __construct($key = null, $value = null, CI $client = null) {
            $this->setKey($key)->setValue($value)->setClient($client);
        }

        /**
         * Returns the key lifetime
         * @author Art <a.molcanovas@gmail.com>
         * @return int
         */
        function getLifetime() {
            return $this->lifetime;
        }

        /**
         * Sets the cache item timeout
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param int|DateTime $timeout Either the timeout in seconds or a DateTime object of when the key should expire
         *
         * @return bool
         */
        function setLifetime($timeout) {
            $time = time();

            if ($timeout instanceof DateTime) {
                $timeout = $timeout->getTimestamp();

                if ($time > $timeout) {
                    trigger_error('The timeout cannot be in the past', E_USER_WARNING);

                    return false;
                }
            } else {
                $timeout = (int)$timeout - $time;
            }

            $this->lifetime = $timeout;

            return true;
        }

        /**
         * Returns the cache client instance
         * @author Art <a.molcanovas@gmail.com>
         * @return CI|null
         */
        function getClient() {
            return $this->client;
        }

        /**
         * Sets the cache client
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $client
         *
         * @return self
         */
        function setClient(CI $client) {
            $this->client = $client;

            return $this;
        }

        /**
         * Returns the set key
         * @author Art <a.molcanovas@gmail.com>
         * @return string
         */
        function getKey() {
            return $this->key;
        }

        /**
         * Sets the key
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key The key
         *
         * @return self
         */
        function setKey($key) {
            $this->key = $key;

            return $this;
        }

        /**
         * Returns the value
         * @author Art <a.molcanovas@gmail.com>
         * @return mixed
         */
        function getValue() {
            return $this->value;
        }

        /**
         * Sets the value
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param mixed $value The value
         *
         * @return self
         */
        function setValue($value) {
            $this->value = $value;

            return $this;
        }

        /**
         * Checks if $this->key exists on the server
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $server The server to use; if omitted, $this->client will be used
         *
         * @return bool
         * @throws Cex If the client or the key isn't set
         */
        function exists(CI $server = null) {
            $this->checkVars();
            if (!$server) {
                $this->checkClient();
                $client = &$this->client;
            } else {
                $client = &$server;
            }

            return $client->exists([$this->key]);
        }

        /**
         * Check if the variables are set correctly, format $this->value & $this->lifetime
         * @author Art <a.molcanovas@gmail.com>
         * @return self
         * @throws Cex If $this->key isn't set
         */
        private function checkVars() {
            if (!$this->key) {
                throw new Cex('The cache key is not set!', Cex::E_NO_KEY);
            } else {
                if ($this->value === null) {
                    $this->value = '';
                }
                if (!is_numeric($this->lifetime)) {
                    $this->lifetime = (int)$this->lifetime;
                }
            }

            return $this;
        }

        /**
         * Check if the client is set correctly
         * @author Art <a.molcanovas@gmail.com>
         * @return self
         * @throws Cex if it isn't
         */
        private function checkClient() {
            if (!$this->client || !($this->client instanceof CI)) {
                throw new Cex('The client is not set correctly.', Cex::E_CLIENT_NOT_SET);
            }

            return $this;
        }

        /**
         * Delete the key from the server
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $server The server to use; if omitted; $this->client will be used
         *
         * @return bool true if the key existed, false if it didn't
         * @throws Cex If the client or the key isn't set
         */
        function delete(CI $server = null) {
            $this->checkVars();
            if (!$server) {
                $this->checkClient();
                $client = &$this->client;
            } else {
                $client = &$server;
            }

            $ret = $client->exists($this->key);
            $client->delete($this->key);

            return $ret;
        }

        /**
         * Saves the item to the server
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $server The server to use; if omitted; $this->client will be used
         *
         * @return bool
         * @throws Cex If the client or the key isn't set
         */
        function saveToServer(CI $server = null) {
            $this->checkVars();
            if (!$server) {
                $this->checkClient();
                $client = &$this->client;
            } else {
                $client = &$server;
            }

            return $client->setKey($this->key, $this->value, $this->lifetime);
        }

        /**
         * Sets $this->value & $this->lifetime from the server
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $server The server to fetch from; if omitted, $this->client will be used.
         *
         * @return bool true if the key exists, false if it doesn't
         * @throws Cex If the client or the key isn't set
         * @uses   CacheItem::getLifetimeFromServer()
         * @uses   CacheItem::getValueFromServer()
         */
        function getFromServer(CI $server = null) {
            $ttl = $this->getLifetimeFromServer($server);
            $val = $this->getValueFromServer($server);

            return $ttl !== 0 && $val !== null;
        }

        /**
         * Sets $this->lifetime from the server
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $server The server to fetch from; of omitted, $this->client will be used
         *
         * @return int the remaining lifetime if the key exists, 0 if it doesn't
         * @throws Cex If the client or the key isn't set
         */
        function getLifetimeFromServer(CI $server = null) {
            $this->checkVars();
            if (!$server) {
                $this->checkClient();
                $client = &$this->client;
            } else {
                $client = &$server;
            }

            $this->lifetime = $client->exists([$this->key]) ? $client->getRemainingLifetime($this->key) : 0;

            return $this->lifetime;
        }

        /**
         * Sets $this->value from the server
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $server The server to fetch from; of omitted, $this->client will be used
         *
         * @return mixed|null the value if the key exists, null if it doesn't
         * @throws Cex If the client or the key isn't set
         */
        function getValueFromServer(CI $server = null) {
            $this->checkVars();
            if (!$server) {
                $this->checkClient();
                $client = &$this->client;
            } else {
                $client = &$server;
            }

            $this->value = $client->exists([$this->key]) ? $client->getKey($this->key) : null;

            return $this->value;
        }
    }
