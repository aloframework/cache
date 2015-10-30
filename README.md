# AloFramework | Cache #

Redis cache manager

Latest release API documentation: [https://aloframework.github.io/cache/](https://aloframework.github.io/cache/)

[![License](https://poser.pugx.org/aloframework/cache/license?format=plastic)](https://www.gnu.org/licenses/gpl-3.0.en.html)
[![Latest Stable Version](https://poser.pugx.org/aloframework/cache/v/stable?format=plastic)](https://packagist.org/packages/aloframework/cache)
[![Total Downloads](https://poser.pugx.org/aloframework/cache/downloads?format=plastic)](https://packagist.org/packages/aloframework/cache)

|                                                                                         dev-develop                                                                                         |                                                                                   Latest release                                                                                   |
|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|:----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
|                              [![Dev Build Status](https://travis-ci.org/aloframework/cache.svg?branch=develop)](https://travis-ci.org/aloframework/cache)                             |                        [![Release Build Status](https://travis-ci.org/aloframework/cache.svg?branch=master)](https://travis-ci.org/aloframework/cache)                       |
| [![SensioLabsInsight](https://insight.sensiolabs.com/projects/21405fd8-0290-4ec9-94e5-c19e43e885d0/mini.png)](https://insight.sensiolabs.com/projects/21405fd8-0290-4ec9-94e5-c19e43e885d0) |                        [![SensioLabsInsight](https://i.imgur.com/KygqLtf.png)](https://insight.sensiolabs.com/projects/21405fd8-0290-4ec9-94e5-c19e43e885d0)                       |
| [![Coverage Status](https://coveralls.io/repos/aloframework/cache/badge.svg?branch=develop&amp;service=github)](https://coveralls.io/github/aloframework/cache?branch=develop)        | [![Coverage Status](https://coveralls.io/repos/aloframework/cache/badge.svg?branch=master&amp;service=github)](https://coveralls.io/github/aloframework/cache?branch=master) |

## Installation ##
Installation is available via Composer:

    composer require aloframework/cache


## Usage ##
Only Redis is supported at this time.

    <?php
    
        use AloFramework\Cache\Clients\RedisClient;
    
        $redis = new RedisClient();
        $redis->connect('localhost');
    
        $redis->setKey('foo', 'bar', 300); // expire the key in 5 minutes
        $redis->setKey('foo', 'bar', new DateTime('2015-01-01 05:05:05')); //Expire the key on 2015-01-01 05:05:05
        $redis->setKey('foo', 'bar'); //Use default expiration time
        $redis['foo'] = 'bar'; //Use default expiration time
    
        //Echo the keys
        echo $redis->getKey('foo');
        echo $redis['foo'];
    
        //Echo all the keys
        print_r($redis->getAll());
    
        //Loop through all the keys
        foreach ($redis as $k => $v) {
            echo 'Key: ' . $k . ', Value: ' . $v . '<br/>';
        }
    
        //Count the number of items in the database
        echo count($redis);
    
        //Or do anything you would with the standard Redis class - RedisClient extends it.


## Configuration ##
Configuration is done via the [Configuration class](https://github.com/aloframework/config).
