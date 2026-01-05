<?php

namespace Fusio\Adapter\Util\Component;

use Predis\Client as PredisClient;

class AccountKeeper
{
    private static ?AccountKeeper $instance = null;

    public static function getInstance(): AccountKeeper
    {
        if (!isset(self::$instance)) {
            self::$instance = new AccountKeeper();
        }

        return self::$instance;
    }

    private PredisClient $cache;


    private function __construct()
    {
        $this->cache = new PredisClient([
            'scheme' => $_ENV['REDIS_SCHEME'] ?? 'tcp',
            'host' => $_ENV['REDIS_HOST'], 'localhost',
            'port' => $_ENV['REDIS_PORT'], 6379,
        ], [
            'prefix' => $_ENV['REDIS_PREFIX_SSO_SERVICE'], '',
        ]);
    }

    public function queryAccessToken(string $userName): string
    {
        $accessToken = $this->cache->get($userName);
        if ($accessToken == null) {
            return '';
        }
        return $accessToken;
    }
}