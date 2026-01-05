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
 * for cloudsql, us `/cloudsql/INSTANCE_NAME` for `HOST`
 * $bolt->cache->initPg(HOST, USER, PASS, NAME)
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

    private array $localcache = [];
    private array $maps = [];
    private array $expires = [];
    private array $notfound = [];
    private int $queries = 0;
    private int $local_hits = 0;
    private int $hydrate_redis = 0;
    private int $hydrate_db = 0;
    private Predis $redis;
    private PDO $pg;

    public function __construct() {
    }

    public function initRedis(string $redis_host) : void {
        $this->redis = new Predis(['scheme' => 'tcp', 'host' => $redis_host, 'port' => 6379]);
    }

    public function initPg(string $host, string $user, string $pass, string $name) : void {
        $this->pg = new PDO("pgsql:host=$host;dbname=$name;user=$user;password=$pass");
    }

    public function setMap(string $table, array $cols) : void {
        $this->maps[$table] = $cols;
    }

    public function setExpire(string $table, int $seconds) : void {
        $this->expires[$table] = $seconds;
    }

    public function get(string $table, string $col, mixed $value): ?object {
        $this->queries++;
        $key = "$table:$col:$value";
        if (!isset($this->notfound[$key])) {
            if (isset($this->localcache[$key])) {
                $this->local_hits++;
                return $this->localcache[$key];
            }
            if ($this->redis->exists($key)) {
                $this->hydrate_redis++;
                $this->localcache[$key] = json_decode($this->redis->get($key));
                return $this->localcache[$key];
            }
            $f = $this->maps[$table] ? implode(',', $this->maps[$table]) : '*';
            if ($q = $this->pg->query("SELECT $f FROM $table WHERE $col = '$value' LIMIT 1")) {
                if ($r = $q->fetch(PDO::FETCH_OBJ)) {
                    $this->hydrate_db++;
                    $expire = $this->expires[$table] ?? 43200;
                    $this->redis->set($key, json_encode($r), 'EX', $expire);
                    $this->localcache[$key] = $r;
                } else {
                    $this->notfound[$key] = true;
                }
            }
        }
        return $this->localcache[$key] ?? null;
    }

    public function stats() : array {
        return [
            'queries' => $this->queries,
            'localcache' => count($this->localcache),
            'local_hits' => $this->local_hits,
            'hydrate_redis' => $this->hydrate_redis,
            'hydrate_db' => $this->hydrate_db,
            'notfound' => count($this->notfound),
        ];
    }
}
