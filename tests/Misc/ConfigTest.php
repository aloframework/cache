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

    namespace AloFramework\Cache\Tests\Misc;

    use AloFramework\Cache\Config\RedisConfig;
    use PHPUnit_Framework_TestCase;

    class ConfigTest extends PHPUnit_Framework_TestCase {

        function testConfig() {
            $this->assertEquals([RedisConfig::CFG_IP      => '127.0.0.1',
                                 RedisConfig::CFG_PORT    => 6379,
                                 RedisConfig::CFG_TIMEOUT => 300],
                                (new RedisConfig())->getAll());
        }
    }
