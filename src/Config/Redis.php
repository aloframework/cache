<?php

    namespace AloFramework\Cache\Config;

    use AloFramework\Config\AbstractConfig;

    /**
     * Redis configuration
     * @author Art <a.molcanovas@gmail.com>
     * @property string $ip      The IP to use
     * @property string $port    The port to use
     * @property int    $timeout The default timeout in seconds
     */
    class Redis extends AbstractConfig {

        /**
         * Default IP config key
         * @var string
         */
        const CFG_IP = 'ip';

        /**
         * Default port config entry
         * @var string
         */
        const CFG_PORT = 'port';

        /**
         * The default timeout in seconds
         * @var string
         */
        const CFG_TIMEOUT = 'timeout';

        /**
         * Default config
         * @var array
         */
        private static $defaults;

        /**
         * Constructor
         * @author Art <a.molcanovas@gmail.com>
         *
         * @param array $cfg Your custom config overrides
         */
        function __construct(array $cfg = []) {
            self::setDefaultConfig();
            parent::__construct(self::$defaults, $cfg);
        }

        /**
         * Sets the default configuration array
         * @author Art <a.molcanovas@gmail.com>
         */
        private static function setDefaultConfig() {
            if (!self::$defaults) {
                self::$defaults = [self::CFG_IP      => '127.0.0.1',
                                   self::CFG_PORT    => 6379,
                                   self::CFG_TIMEOUT => 300];
            }
        }
    }
