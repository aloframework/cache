<?php

    namespace AloFramework\Cache\Config;

    use AloFramework\Config\AbstractConfig;

    /**
     * Redis configuration
     * @author Art <a.molcanovas@gmail.com>
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
    }
