<?php

    namespace AloFramework\Cache\Clients;

    use AloFramework\Cache\CacheException;
    use AloFramework\Cache\ClientInterface;
    use AloFramework\Cache\Config\RedisConfig as Config;
    use AloFramework\Common\Alo;
    use AloFramework\Config\Configurable;
    use AloFramework\Config\ConfigurableTrait;
    use AloFramework\Log\Log;
    use ArrayIterator;
    use DateTime;
    use Psr\Log\LoggerInterface;
    use Redis;
    use Traversable;

    /**
     * A Redis-based cache client
     * @author Art <a.molcanovas@gmail.com>
     */
    class RedisClient extends Redis implements ClientInterface, Configurable {

        use ConfigurableTrait;

        /**
         * Logger
         * @var LoggerInterface
         */
        protected $log;

        /**
         * Contructor
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param Config          $config Config object
         * @param LoggerInterface $logger User-supplied logging object. AloFramework\Log will be used if one isn't
         *                                supplied
         *
         * @throws CacheException If the Redis PHP extension isn't enabled
         */
        function __construct(Config $config = null, LoggerInterface $logger = null) {
            parent::__construct();
            //@codeCoverageIgnoreStart
            if (!class_exists('\Redis')) {
                throw new CacheException('The Redis extension must be enabled and running',
                                         CacheException::E_EXTENSION_DISABLED);
            } else {
                //@codeCoverageIgnoreEnd
                $this->log    = Alo::ifnull($logger, new Log());
                $this->config = Alo::ifnull($config, new Config());
            }
        }

        /**
         * Connect to the cache server
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $ip   Server IP
         * @param int    $port Server port
         *
         * @return bool Whether the connection succeeded
         */
        function connect($ip = null, $port = null) {
            $ip   = Alo::ifnull($ip, $this->config->ip, true);
            $port = Alo::ifnull($port, $this->config->port, true);

            $con = parent::connect($ip, $port);

            if ($con) {
                $this->log->debug('Connected to Redis @ ' . $ip . ':' . $port);
            } else {
                $this->log->critical('Failed to connect to Redis @ ' . $ip . ':' . $port);
            }

            return $con;
        }

        /**
         * Count elements of an object
         * @link  http://php.net/manual/en/countable.count.php
         * @return int
         */
        function count() {
            return $this->dbSize();
        }

        /**
         * Deletes a cached item
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string|array $key Item key or array of keys
         *
         * @return self
         */
        function delete($key) {
            if (is_array($key)) {
                $this->log->info('Deleting the following Redis keys: ' . implode(', ', $key));
                call_user_func_array('parent::delete', $key);
            } else {
                $this->log->info('Deleting Redis key ' . $key);
                parent::delete($key);
            }

            return $this;
        }

        /**
         * Returns a cached item
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key Item key
         *
         * @return mixed The item or null if it's not found
         */
        function getKey($key) {
            return $this->get($key);
        }

        /**
         * Get the value related to the specified key
         * @author  Art <a.molcanovas@gmail.com>
         *
         * @param   string $key
         *
         * @return  mixed|bool: If key didn't exist, FALSE is returned. Otherwise, the value related to this key is
         * returned.
         * @link    http://redis.io/commands/get
         * @example $redis->get('key');
         */
        function get($key) {
            $get = parent::get($key);
            self::decode($get);

            return $get;
        }

        /**
         * Get the values of all the specified keys. If one or more keys dont exist, the array will contain FALSE at the
         * position of the key.
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param   array $keys Array containing the list of the keys
         *
         * @return  array Array containing the values related to keys in argument
         * @example
         * <pre>
         * $redis->set('key1', 'value1');
         * $redis->set('key2', 'value2');
         * $redis->set('key3', 'value3');
         * $redis->getMultiple(array('key1', 'key2', 'key3')); // array('value1', 'value2', 'value3');
         * $redis->getMultiple(array('key0', 'key1', 'key5')); // array(`FALSE`, 'value2', `FALSE`);
         * </pre>
         */
        function getMultiple(array $keys) {
            $get = parent::getMultiple($keys);

            if ($get) {
                foreach ($get as &$v) {
                    self::decode($v);
                }
            }

            return $get;
        }

        /**
         * Sets a cached item
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string       $key      Item key
         * @param mixed        $value    Item value
         * @param int|DateTime $timeout  Expiration time in seconds, or a DateTime object for when it's supposed to
         *                               expire
         *
         * @return bool
         */
        function setKey($key, $value, $timeout = null) {
            return $this->formatTimeout($timeout) && $this->setex($key, $timeout, $value);
        }

        /**
         * Formats the timeout
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param int|DateTime $timeout The timeout
         *
         * @return bool false if an error occurred
         */
        private function formatTimeout(&$timeout = null) {
            if ($timeout instanceof DateTime) {
                $time    = time();
                $timeout = $timeout->getTimestamp();

                if ($time > $timeout) {
                    trigger_error('The timeout cannot be in the past', E_USER_WARNING);

                    //@codeCoverageIgnoreStart
                    return false;
                    //@codeCoverageIgnoreEnd
                } else {
                    $timeout = $timeout - $time;
                }
            } else {
                $timeout = Alo::ifnull($timeout, $this->config->timeout, true);
            }

            return true;
        }

        /**
         * Set the string value in argument as value of the key, with a time to live.
         * @author  Art <a.molcanovas@gmail.com>
         *
         * @param   string $key   Key to set
         * @param   int    $ttl   Lifetime
         * @param   mixed  $value Value to set. Non-scalar values will be json-encoded
         *
         * @return  bool:   TRUE if the command is successful.
         * @link    http://redis.io/commands/setex
         * @example $redis->setex('key', 3600, 'value'); // sets key → value, with 1h TTL.
         */
        function setex($key, $ttl, $value) {
            self::encode($value);

            return parent::setex($key, $ttl, $value);
        }

        /**
         * Set the string value in argument as value of the key if the key doesn't already exist in the database.
         *
         * @param   string $key   The key
         * @param   string $value The value. If the value isn't scalar it will be json_encoded
         *
         * @return  bool:   TRUE in case of success, FALSE in case of failure.
         * @link    http://redis.io/commands/setnx
         * @example
         * <pre>
         * $redis->setnx('key', 'value');   // return TRUE
         * $redis->setnx('key', 'value');   // return FALSE
         * </pre>
         */
        function setnx($key, $value) {
            self::encode($value);

            return parent::setnx($key, $value);
        }

        /**
         * Set the string value in argument as value of the key.
         * @author  Art <a.molcanovas@gmail.com>
         *
         * @param   string $key   Key to set
         * @param   string $value Value to set. If the value isn't scalar it will be json_encoded
         * @param   int    $ttl   [optional] Calling setex() is preferred if you want a timeout.
         *
         * @return  bool:   TRUE if the command is successful.
         * @link    http://redis.io/commands/set
         * @example $redis->set('key', 'value');
         */
        function set($key, $value, $ttl = 0) {
            self::encode($value);

            return parent::set($key, $value, $ttl);
        }

        /**
         * Encodes a value to store
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param mixed $value The value
         */
        private static function encode(&$value) {
            if (!is_scalar($value)) {
                $value = json_encode($value);
            }
        }

        /**
         * Decodes a stored value
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param mixed $value Reference to the value
         */
        private static function decode(&$value) {
            if (is_string($value)) {
                $dec = json_decode($value, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $dec;
                }
            }
        }

        /**
         * Returns all the cached items as an associative array
         * @author Art <a.molcanovas@gmail.com>
         * @return array
         */
        function getAll() {
            $get = $this->keys('*');
            $r   = [];

            if ($get) {
                foreach ($get as $k) {
                    $r[$k] = $this->getKey($k);
                }
            }

            return $r;
        }

        /**
         * Purges all cached items
         * @author Art <a.molcanovas@gmail.com>
         * @return bool
         */
        function purge() {
            $p = $this->flushAll();

            if ($p) {
                $this->log->notice('Purged Redis');
                //@codeCoverageIgnoreStart
            } else {
                $this->log->error('Failed to purge Redis');
            }

            //@codeCoverageIgnoreEnd

            return $p;
        }

        /**
         * Retrieve an external iterator
         * @link   http://php.net/manual/en/iteratoraggregate.getiterator.php
         * @author Art <a.molcanovas@gmail.com>
         * @return Traversable An instance of an object implementing <b>Iterator</b> or
         * <b>Traversable</b>
         */
        function getIterator() {
            return new ArrayIterator($this->getAll());
        }

        /**
         * Whether a offset exists
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/arrayaccess.offsetexists.php
         *
         * @param mixed $offset The key
         *
         * @return boolean
         */
        function offsetExists($offset) {
            return $this->exists($offset);
        }

        /**
         * Offset to retrieve
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/arrayaccess.offsetget.php
         *
         * @param mixed $offset The key
         *
         * @return mixed
         */
        function offsetGet($offset) {
            return $this->getKey($offset);
        }

        /**
         * Check if the key exists
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key The key
         *
         * @return bool
         */
        function exists($key) {
            return parent::exists($key);
        }

        /**
         * Offset to set
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/arrayaccess.offsetset.php
         *
         * @param string $offset The key
         * @param mixed  $value  Value to set
         *
         * @return void
         */
        function offsetSet($offset, $value) {
            $this->setKey($offset, $value);
        }

        /**
         * Offset to unset
         * @author Art <a.molcanovas@gmail.com>
         * @link   http://php.net/manual/en/arrayaccess.offsetunset.php
         *
         * @param mixed $offset The key
         *
         * @return void
         */
        function offsetUnset($offset) {
            $this->delete($offset);
        }

        /**
         * Returns how many seconds this key has left before expiring
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key The key
         *
         * @return int The remaining lifetime in seconds. If the key doesn't exist 0 is returned.
         */
        function getRemainingLifetime($key) {
            return (int)$this->ttl($key);
        }

    }
