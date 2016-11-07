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

    use AloFramework\Cache\CacheItem as CI;
    use AloFramework\Cache\Clients\RedisClient as Redis;
    use DateInterval as DI;
    use DateTime as DT;
    use PHPUnit_Framework_TestCase;

    class CacheItemTest extends PHPUnit_Framework_TestCase {

        /** @var Redis */
        private $r;

        function __construct($name = null, array $data = [], $dataName = '') {
            parent::__construct($name, $data, $dataName);
            $this->r = new Redis();
            $this->r->connect();
        }

        function testGettersSetters() {
            $ci = new CI(__METHOD__, __CLASS__, $this->r);
            $ci->lifetime = (new DT())->add(new DI('PT30S'));

            $this->assertEquals(30, $ci->lifetime);
            $this->assertEquals(30, $ci->getLifetime());

            $this->assertEquals(__METHOD__, $ci->key);
            $this->assertEquals(__METHOD__, $ci->getKey());

            $this->assertEquals(__CLASS__, $ci->value);
            $this->assertEquals(__CLASS__, $ci->getValue());

            $this->assertEquals($this->r, $ci->client);
            $this->assertEquals($this->r, $ci->getClient());
        }

        /**
         * @expectedException \PHPUnit_Framework_Error
         * @expectedExceptionMessage The timeout cannot be in the past
         */
        function testSetLifetimePast() {
            $ci = new CI(__METHOD__, __CLASS__, $this->r);
            $ci->lifetime = (new DT())->sub(new DI('PT30S'));
        }

        /**
         * @expectedException \InvalidArgumentException
         * @expectedExceptionMessage The property does not exist: foo
         */
        function testInvalidMagicGet() {
            (new CI())->foo;
        }

        /**
         * @expectedException \InvalidArgumentException
         * @expectedExceptionMessage The property does not exist: foo
         */
        function testInvalidMagicSet() {
            $ci = new CI();
            $ci->foo = 'bar';
        }

        function testExists() {
            $this->r->purge();
            $this->r[__METHOD__] = 1;

            $ci = new CI(__METHOD__, null, $this->r);
            $this->assertTrue($ci->exists());
            $this->assertTrue($ci->exists($this->r));

            $this->r->delete(__METHOD__);
            $this->assertFalse($ci->exists());
        }

        function testNonNumericLifetimeAndDelete() {
            $this->r->delete(__METHOD__);
            $ci = new CI(__METHOD__, 1, $this->r);
            $ci->setLifetime('foo');

            $this->assertEquals('foo', $ci->lifetime);

            $this->assertFalse($ci->delete());
            $this->assertFalse($ci->exists());
            $this->assertEquals(0, $ci->lifetime);
            $ci->lifetime = 10;

            $this->assertTrue($ci->saveToServer());
            $this->assertTrue($ci->exists());
            $this->assertEquals(1, $this->r->get(__METHOD__));
            $this->assertTrue($ci->delete());

            $this->assertTrue($ci->saveToServer($this->r));
            $this->assertTrue($ci->exists($this->r));
            $this->assertEquals(1, $this->r->get(__METHOD__));
            $this->assertTrue($ci->delete($this->r));
        }

        function testGetFromServer() {
            $this->r->purge();
            $this->r->setKey(__METHOD__ . '1', 1, 10);
            $this->r->setKey(__METHOD__ . '2', 2, 10);

            $ci1 = new CI(__METHOD__ . '1', 1, $this->r);
            $ci2 = new CI(__METHOD__ . '2', 2);

            $ci1->getFromServer();
            $this->assertEquals(1, $ci1->value);
            $this->assertEquals(10, $ci1->lifetime);

            $ci2->getFromServer($this->r);
            $this->assertEquals(2, $ci2->value);
            $this->assertEquals(10, $ci2->lifetime);
        }

        /**
         * @expectedException \AloFramework\Cache\CacheException
         * @expectedExceptionMessage The cache key is not set!
         */
        function testExistsBadNoKey() {
            $ci = new CI();
            $ci->exists();
        }

        /**
         * @expectedException \AloFramework\Cache\CacheException
         * @expectedExceptionMessage The client is not set correctly.
         */
        function testExistsBadNoClient() {
            $ci = new CI(__METHOD__);
            $ci->exists();
        }
    }
