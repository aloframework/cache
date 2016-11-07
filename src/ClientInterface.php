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

    use ArrayAccess;
    use Countable;
    use IteratorAggregate;

    /**
     * The AloFramework cache interface
     *
     * @author Art <a.molcanovas@gmail.com>
     */
    interface ClientInterface extends ArrayAccess, Countable, IteratorAggregate {

        /**
         * Connect to the cache server
         *
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
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string|array $key Item key or array of keys
         *
         * @return ClientInterface
         */
        public function delete($key);

        /**
         * Check if the key exists
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key The key
         *
         * @return bool
         */
        public function exists($key);

        /**
         * Returns a cached item
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key Item key
         *
         * @return mixed The item or null if it's not found
         */
        public function getKey($key);

        /**
         * Returns all the cached items as an associative array
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return array
         */
        public function getAll();

        /**
         * Purges all cached items
         *
         * @author Art <a.molcanovas@gmail.com>
         * @return bool
         */
        public function purge();

        /**
         * Sets a cached item
         *
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

        /**
         * Returns how many seconds this key has left before expiring
         *
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key The key
         *
         * @return int The remaining lifetime in seconds. If the key doesn't exist 0 is returned.
         */
        public function getRemainingLifetime($key);
    }
