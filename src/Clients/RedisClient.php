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
        public function connect($ip = null, $port = null) {
            return parent::connect(Alo::ifnull($ip, $this->config->ip, true),
                                   Alo::ifnull($port, $this->config->port, true));
        }

        /**
         * Magically gets a cached item
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key Item key
         *
         * @return mixed
         */
        public function __get($key) {
            return $this->get($key);
        }

        /**
         * Magically sets a cached item
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string $key   Item key
         * @param mixed  $value Item value
         */
        public function __set($key, $value) {
            $this->set($key, $value);
        }

        /**
         * Deletes a cached item
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param string|array $key Item key or array of keys
         *
         * @return self
         */
        public function delete($key) {
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
        public function get($key) {
            return parent::get($key);
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
        public function set($key, $value, $timeout = null) {
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

            return parent::set($key, $value, $timeout);
        }

        /**
         * Count elements of an object
         * @link  http://php.net/manual/en/countable.count.php
         * @return int The custom count as an integer.
         * </p>
         * <p>
         * The return value is cast to an integer.
         */
        public function count() {
            return parent::dbSize();
        }

        /**
         * Returns all the cached items as an associative array
         * @author Art <a.molcanovas@gmail.com>
         * @return array
         */
        public function getAll() {
            $get = parent::keys('*');
            $r   = [];

            if ($get) {
                foreach ($get as $k) {
                    $r[$k] = $this->get($k);
                }
            }

            return $r;
        }



        /**
         * Purges all cached items
         * @author Art <a.molcanovas@gmail.com>
         * @return bool
         */
        public function purge() {
            return parent::flushAll();
        }

        /**
         * Retrieve an external iterator
         * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
         * @return Traversable An instance of an object implementing <b>Iterator</b> or
         * <b>Traversable</b>
         */
        public function getIterator() {
            return new ArrayIterator($this->getAll());
        }

        /**
         * Whether a offset exists
         * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
         *
         * @param mixed $offset <p>
         *                      An offset to check for.
         *                      </p>
         *
         * @return boolean true on success or false on failure.
         * </p>
         * <p>
         * The return value will be casted to boolean if non-boolean was returned.
         */
        public function offsetExists($offset) {
            return Alo::get($this->get($offset)) !== null;
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
        public function offsetGet($offset) {
            return $this->get($offset);
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
        public function offsetSet($offset, $value) {
            $this->set($offset, $value);
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
        public function offsetUnset($offset) {
            $this->delete($offset);
        }

    }
