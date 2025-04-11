<?php

namespace Pota\Bolt;

class Config {

    private array $data = [];

    public function get(string $key = null) : mixed {
        $key = trim(strtolower($key));
        if (!empty($key)) {
            return array_key_exists($key, $this->data) ? $this->data[$key] : null;
        }
        return $this->data;
    }

    public function set(string $key, mixed $value) : void {
        $key = trim(strtolower($key));
        $this->data[$key] = $value;
    }

    public function setArray(array $data) : void {
        foreach ($data as $k => $v) {
            $this->set($k, $v);
        }
    }

    public function loadIni(string $file) : void {
        if (is_file($file) && is_readable($file)) {
            $this->setArray(parse_ini_file($file));
        }
    }

}
