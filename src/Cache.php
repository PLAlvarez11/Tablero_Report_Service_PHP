<?php
declare(strict_types=1);

namespace App;

use Redis;

final class Cache
{
    private Redis $r;
    private int $ttl;

    public function __construct()
    {
        $this->r = new Redis();
        $this->r->connect(getenv('REDIS_HOST') ?: 'redis-cache', (int)(getenv('REDIS_PORT') ?: 6379));
        $db = (int)(getenv('REDIS_DB') ?: 0);
        $this->r->select($db);
        $this->ttl = (int)(getenv('REDIS_TTL_SECONDS') ?: 86400);
    }

    public function get(string $ns, array $payload): ?array
    {
        $key = $this->key($ns, $payload);
        $raw = $this->r->get($key);
        return $raw ? json_decode($raw, true) : null;
    }

    public function set(string $ns, array $payload, array $value): void
    {
        $key = $this->key($ns, $payload);
        $this->r->setex($key, $this->ttl, json_encode($value, JSON_UNESCAPED_UNICODE));
    }

    private function key(string $ns, array $payload): string
    {
        ksort($payload);
        return $ns . ':' . hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE));
    }
}
