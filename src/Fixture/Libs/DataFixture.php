<?php

namespace PhpLab\Eloquent\Fixture\Libs;

class DataFixture
{

    private $data;
    private $deps;

    public function __construct($data = [], array $deps = [])
    {
        $this->data = $data;
        $this->deps = $deps;
    }

    public function run() {
        return $this->data;
    }

    public function deps() {
        return $this->deps;
    }
}
