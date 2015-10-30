<?php

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
