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

    /**
     * Cache-related exceptions
     *
     * @author Art <a.molcanovas@gmail.com>
     */
    class CacheException extends \Exception {

        /**
         * Code when a required extension is not installed or enabled
         *
         * @var int
         */
        const E_EXTENSION_DISABLED = 1;

        /**
         * Code when the client is not set in CacheItem
         *
         * @var int
         */
        const E_CLIENT_NOT_SET = 2;

        /**
         * Code when no key is set
         *
         * @var int
         */
        const E_NO_KEY = 3;
    }
