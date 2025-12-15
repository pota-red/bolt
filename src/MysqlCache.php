<?php

namespace Pota\Bolt;

class MysqlCache {

    private $conn = null;

    private array $data = [];

    public function __construct($conn, string $database_name) {
        $this->conn = $conn;
    }

    public function get(string $table, string $key, mixed $value) {
        $table = trim(strtolower($table));
        $key = trim(strtolower($key));
        $cache_key = "$table:$value";
        if (!array_key_exists($cache_key, $this->data)) {
            $value = $this->conn->real_escape_string($value);
            if ($q = $this->conn->query("SELECT * FROM $table WHERE `$key`='$value' LIMIT 1")) {
                if ($r = $q->fetch_assoc()) {
                    $this->data[$cache_key] = $r;
                } else {
                    $this->data[$cache_key] = null;
                }
            } else {
                $this->data[$cache_key] = null;
            }
        }
        return array_key_exists($cache_key, $this->data) ? $this->data[$cache_key] : null;
    }

}

