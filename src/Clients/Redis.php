<?php

    namespace AloFramework\Cache\Clients;

    use AloFramework\Cache\ClientInterface;
    use Redis as Client;

    class Redis implements ClientInterface {

        /**
         * Redis client
         * @var Client
         */
        private $client;

        function __construct() {
            $this->client = new Client();
        }

    }
