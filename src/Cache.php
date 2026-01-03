<?php

/**
 * Usage
 *
 * init with $bolt::__construct(['cache'])
 *
 * Init redis
 * $bolt->cache->initRedis(REDIS_HOST)
 *
 * Init PG
 * $bolt->cache->initPg(PG_INSTANCE, PG_USER, PG_PASS, PG_NAME)
 *
 * Set fields to fetch from table (defaults to `*` for all tables if not specified)
 * $bolt->cache->setMap('callsigns', ['id', 'account_id', 'label', 'primary'])
 *
 * Override default expire (defaults to `43200` if not overwritten by this)
 * $bolt->cache->setExpire('callsigns', 3600)
 *
 * Use cache
 * $bolt->cache->get('callsigns', 'label', 'VE3JLN')
 */

namespace Pota\Bolt;

use PDO;
use Predis\Client as Predis;

class Cache {

    private array $data = [];
    private array $maps = [];
    private array $expires = [];
    private Predis $redis;
    private PDO $pg;

    public function __construct() {
    }

    public function initRedis(string $redis_host) : void {
        $this->redis = new Predis(['scheme' => 'tcp', 'host' => $redis_host, 'port' => 6379]);
    }

    public function initPg(string $pg_instance, string $pg_user, string $pg_pass, string $pg_name) : void {
        $this->pg = new PDO("pgsql:dbname=$pg_name;host=/cloudsql/$pg_instance", $pg_user, $pg_pass);
    }

    public function setMap(string $table, array $cols) : void {
        $this->maps[$table] = $cols;
    }

    public function setExpire(string $table, int $seconds) : void {
        $this->expires[$table] = $seconds;
    }

    public function get(string $table, string $col, mixed $value): ?object {
        $key = "$table:$col:$value";
        if (!isset($this->data[$key])) {
            if (!$this->redis->exists($key)) {
                $f = $this->maps[$table] ? implode(',', $this->maps[$table]) : '*';
                if ($q = $this->pg->query("SELECT $f FROM $table WHERE $col = '$value' LIMIT 1")) {
                    if ($r = $q->fetch(PDO::FETCH_OBJ)) {
                        $data = json_encode($r);
                        $expire = $this->expires[$table] ?? 43200;
                        $this->redis->set($key, $data, 'EX', $expire);
                        $this->data[$key] = $data;
                    }
                }
            }
        }
        return isset($this->data[$key]) ? json_decode($this->data[$key]) : null;
    }
}
