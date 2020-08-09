<?php

namespace PhpLab\Eloquent\Fixture\Libs;

interface FixtureInterface
{

    public function load();
    public function unload();
    public function deps();

}
