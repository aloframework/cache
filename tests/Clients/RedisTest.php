<?php

    namespace AloFramework\Cache\Tests\Clients;

    use AloFramework\Cache\Clients\RedisClient as Client;
    use AloFramework\Cache\Config\RedisConfig as Cfg;
    use PHPUnit_Framework_TestCase;
    use DateTime as DT;
    use DateInterval as DI;

    class RedisTest extends PHPUnit_Framework_TestCase {

        /** @var Client */
        private $client;

        function __construct($name = null, array $data = [], $dataName = '') {
            parent::__construct($name, $data, $dataName);
            $this->client = new Client();
            $this->client->connect();
        }

        function testConnect() {
            $this->assertTrue((new Client())->connect());
            $this->assertFalse((new Client())->connect('255.255.255.255', 1));
        }

        function testGetSet() {
            $this->client[__METHOD__] = 'bar';
            $this->assertEquals('bar', $this->client[__METHOD__]);
            $this->assertEquals('bar', $this->client->getKey(__METHOD__));
            $this->assertNotEquals(0, count($this->client));
            $this->assertTrue(isset($this->client[__METHOD__]));
            $this->assertEquals((new Cfg())->timeout, $this->client->getRemainingLifetime(__METHOD__));
        }

        function testPurge() {
            $this->assertTrue($this->client->purge());
            $this->assertEquals(0, count($this->client));
            $this->client[__METHOD__] = 1;
            $this->assertEquals(1, $this->client->count());
            $this->assertTrue($this->client->purge());
            $this->assertEquals(0, count($this->client));
        }

        function testDelete() {
            $this->client->delete(__METHOD__);
            $this->assertFalse(isset($this->client[__METHOD__]));
            $this->client[__METHOD__] = 1;
            $this->assertTrue(isset($this->client[__METHOD__]));
            unset($this->client[__METHOD__]);

            $this->assertFalse($this->client->exists(__METHOD__));
        }

        function testDeleteArray() {
            $this->client->purge();
            $this->client[__METHOD__ . '1'] = 1;
            $this->client[__METHOD__ . '2'] = 2;
            $this->assertEquals(2, count($this->client));
            $this->client->delete([__METHOD__ . '1', __METHOD__ . '2']);
            $this->assertEquals(0, count($this->client));
        }

        function testSetKeyDateTimeValid() {
            $dt = new DT();
            $dt->add(new DI('PT5S'));
            $this->assertTrue($this->client->setKey(__METHOD__, 1, $dt));
            $this->assertTrue(isset($this->client[__METHOD__]));
            $this->assertEquals(5, $this->client->getRemainingLifetime(__METHOD__));
        }

        function testSetKeyDateTimeInvalid() {
            $this->setExpectedException('\PHPUnit_Framework_Error');

            $dt = new DT();
            $dt->sub(new DI('PT5S'));

            $this->assertFalse($this->client->setKey(__METHOD__, 1, $dt));
        }

        function testGetKeys() {
            $this->client->purge();
            $this->assertEquals(0, count($this->client));

            $this->client[__METHOD__ . '1'] = 1;
            $this->client[__METHOD__ . '2'] = 2;

            $this->assertEquals([__METHOD__ . '1' => 1,
                                 __METHOD__ . '2' => 2],
                                $this->client->getAll());
        }

        function testGetIterator() {
            $this->client->purge();
            $this->assertEquals(0, count($this->client));

            $this->client[__METHOD__ . '1'] = 1;
            $this->client[__METHOD__ . '2'] = 2;

            $count = 0;
            foreach ($this->client as $k => $v) {
                $this->assertTrue(in_array($k, [__METHOD__ . '1', __METHOD__ . '2']));
                $this->assertTrue(in_array($v, [1, 2]));
                $count++;
            }

            $this->assertEquals(2, $count);
        }
    }
