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
            if (!class_exists('\Redis')) {
                throw new CacheException('The Redis extension must be enabled and running',
                                         CacheException::E_EXTENSION_DISABLED);
            } else {
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
            return parent::connect(Alo::ifnull($ip, $this->config->ip, true),
                                   Alo::ifnull($port, $this->config->port, true));
        }

        /**
         * Count elements of an object
         * @link  http://php.net/manual/en/countable.count.php
         * @return int The custom count as an integer.
         * </p>
         * <p>
         * The return value is cast to an integer.
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
                call_user_func_array('parent::delete', $key);
            } else {
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
            if ($timeout instanceof DateTime) {
                $time    = time();
                $timeout = $timeout->getTimestamp();

                if ($time > $timeout) {
                    trigger_error('The timeout cannot be in the past', E_USER_WARNING);

                    return false;
                } else {
                    $timeout = $timeout - $time;
                }
            } else {
                $timeout = Alo::ifnull($timeout, $this->config->timeout, true);
            }

            return $this->setex($key, $timeout, $value);
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
            return $this->flushAll();
        }

        /**
         * Retrieve an external iterator
         * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
         * @return Traversable An instance of an object implementing <b>Iterator</b> or
         * <b>Traversable</b>
         */
        function getIterator() {
            return new ArrayIterator($this->getAll());
        }

        /**
         * Whether a offset exists
         * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
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
         * @link  http://php.net/manual/en/arrayaccess.offsetget.php
         *
         * @param mixed $offset <p>
         *                      The offset to retrieve.
         *                      </p>
         *
         * @return mixed Can return all value types.
         */
        function offsetGet($offset) {
            return $this->getKey($offset);
        }

        /**
         * Offset to set
         * @link  http://php.net/manual/en/arrayaccess.offsetset.php
         *
         * @param mixed $offset <p>
         *                      The offset to assign the value to.
         *                      </p>
         * @param mixed $value  <p>
         *                      The value to set.
         *                      </p>
         *
         * @return void
         */
        function offsetSet($offset, $value) {
            $this->setKey($offset, $value);
        }

        /**
         * Offset to unset
         * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
         *
         * @param mixed $offset <p>
         *                      The offset to unset.
         *                      </p>
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
