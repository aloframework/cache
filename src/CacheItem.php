<?php
    /**
 *    Copyright (c) Arturas Molcanovas <a.molcanovas@gmail.com> 2016.
 *    https://github.com/aloframework/cache
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

    namespace AloFramework\Cache;

    use AloFramework\Cache\CacheException as Cex;
    use AloFramework\Cache\ClientInterface as CI;
    use DateTime;
    use InvalidArgumentException as Invalid;

    /**
     * A representation of a cached item
     *
     * @author Art <a.molcanovas@gmail.com>
     * @property string          $key      Cache item key
     * @property mixed           $value    Cache item value
     * @property int             $lifetime Cache item lifetime remaining
     * @property ClientInterface $client   The cache client
     */
    class CacheItem {

        /**
         * The cache client
         *
         * @var ClientInterface
         */
        protected $client;

        /**
         * Cache item key
         *
         * @var string
         */
        private $key;

        /**
         * The cached value
         *
         * @var mixed
         */
        private $value;

        /**
         * The lifetime of the key in seconds
         *
         * @var int
         */
        private $lifetime = 0;

        /**
         * Allowed magic getters/setters
         *
         * @var array
         */
        private static $allowedMagic = ['key', 'lifetime', 'value', 'client'];

        /**
         * Constructor
         *
         * @param string $key    Immediately set the key
         * @param mixed  $value  Immediately set the value
         * @param CI     $client Store a reference to the client
         */
        public function __construct($key = null, $value = null, CI $client = null) {
            $this->setKey($key)->setValue($value);

            if ($client) {
                $this->setClient($client);
            }
        }

        /**
         * Magic getter
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key key to get
         *
         * @return mixed
         * @throws Invalid when the key doesn't exist
         */
        public function __get($key) {
            if (in_array($key, self::$allowedMagic)) {
                return $this->{$key};
            } else {
                throw new Invalid('The property does not exist: ' . $key);
            }
        }

        /**
         * Magic setter
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key   Key to set
         * @param mixed  $value Value to set
         *
         * @throws Invalid when the key doesn't exist
         */
        public function __set($key, $value) {
            if (in_array($key, self::$allowedMagic)) {
                call_user_func([$this, 'set' . ucfirst(strtolower($key))], $value);
            } else {
                throw new Invalid('The property does not exist: ' . $key);
            }
        }

        /**
         * Returns the key lifetime
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return int
         */
        public function getLifetime() {
            return $this->lifetime;
        }

        /**
         * Sets the cache item timeout
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param int|DateTime $timeout Either the timeout in seconds or a DateTime object of when the key should expire
         *
         * @return bool
         */
        public function setLifetime($timeout) {
            $time = time();

            if ($timeout instanceof DateTime) {
                $timeout = $timeout->getTimestamp();

                if ($time > $timeout) {
                    trigger_error('The timeout cannot be in the past', E_USER_WARNING);

                    //@codeCoverageIgnoreStart
                    return false;
                    //@codeCoverageIgnoreEnd
                } else {
                    $timeout = (int)$timeout - $time;
                }
            }

            $this->lifetime = $timeout;

            return true;
        }

        /**
         * Returns the cache client instance
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return CI|null
         */
        public function getClient() {
            return $this->client;
        }

        /**
         * Sets the cache client
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $client
         *
         * @return self
         */
        public function setClient(CI $client) {
            $this->client = $client;

            return $this;
        }

        /**
         * Returns the set key
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return string
         */
        public function getKey() {
            return $this->key;
        }

        /**
         * Sets the key
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key The key
         *
         * @return self
         */
        public function setKey($key) {
            $this->key = $key;

            return $this;
        }

        /**
         * Returns the value
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return mixed
         */
        public function getValue() {
            return $this->value;
        }

        /**
         * Sets the value
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param mixed $value The value
         *
         * @return self
         */
        public function setValue($value) {
            $this->value = $value;

            return $this;
        }

        /**
         * Checks if $this->key exists on the server
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $server The server to use; if omitted, $this->client will be used
         *
         * @return bool
         * @throws Cex If the client or the key isn't set
         */
        public function exists(CI $server = null) {
            $this->checkVars();
            if (!$server) {
                $this->checkClient();
                $client = &$this->client;
            } else {
                $client = &$server;
            }

            return $client->exists($this->key);
        }

        /**
         * Check if the variables are set correctly, format $this->value & $this->lifetime
         *
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
         *
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
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $server The server to use; if omitted; $this->client will be used
         *
         * @return bool true if the key existed, false if it didn't
         * @throws Cex If the client or the key isn't set
         */
        public function delete(CI $server = null) {
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
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $server The server to use; if omitted; $this->client will be used
         *
         * @return bool
         * @throws Cex If the client or the key isn't set
         */
        public function saveToServer(CI $server = null) {
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
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $server The server to fetch from; if omitted, $this->client will be used.
         *
         * @return bool true if the key exists, false if it doesn't
         * @throws Cex If the client or the key isn't set
         * @uses   CacheItem::getLifetimeFromServer()
         * @uses   CacheItem::getValueFromServer()
         */
        public function getFromServer(CI $server = null) {
            $ttl = $this->getLifetimeFromServer($server);
            $val = $this->getValueFromServer($server);

            return $ttl !== 0 && $val !== null;
        }

        /**
         * Sets $this->lifetime from the server
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $server The server to fetch from; of omitted, $this->client will be used
         *
         * @return int the remaining lifetime if the key exists, 0 if it doesn't
         * @throws Cex If the client or the key isn't set
         */
        public function getLifetimeFromServer(CI $server = null) {
            $this->checkVars();
            if (!$server) {
                $this->checkClient();
                $client = &$this->client;
            } else {
                $client = &$server;
            }

            $this->lifetime = $client->exists($this->key) ? $client->getRemainingLifetime($this->key) : 0;

            return $this->lifetime;
        }

        /**
         * Sets $this->value from the server
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param CI $server The server to fetch from; of omitted, $this->client will be used
         *
         * @return mixed|null the value if the key exists, null if it doesn't
         * @throws Cex If the client or the key isn't set
         */
        public function getValueFromServer(CI $server = null) {
            $this->checkVars();
            if (!$server) {
                $this->checkClient();
                $client = &$this->client;
            } else {
                $client = &$server;
            }

            $this->value = $client->exists($this->key) ? $client->getKey($this->key) : null;

            return $this->value;
        }
    }
