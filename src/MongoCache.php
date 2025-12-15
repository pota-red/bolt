<?php

namespace Pota\Bolt;

class MongoCache {

    private $conn = null;

    private string $db_name = '';

    private array $data = [];

    public function __construct($conn, string $database_name) {
        $this->conn = $conn;
        $this->db_name = $database_name;
    }

    public function get(string $collection, string $key, mixed $value) {
        $collection = trim(strtolower($collection));
        $key = trim(strtolower($key));
        $cache_key = "$collection:$value";
        if (!array_key_exists($cache_key, $this->data)) {
            $col = $this->conn->getCollection($this->db_name, $collection);
            if ($col->countDocuments([$key => $value]) > 0) {
                $doc = $col->findOne([$key => $value]);
                $this->data[$cache_key] = (array)$doc;
            } else {
                $this->data[$cache_key] = null;
            }
        }
        return array_key_exists($cache_key, $this->data) ? $this->data[$cache_key] : null;
    }

}

