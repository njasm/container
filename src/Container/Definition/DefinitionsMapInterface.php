<?php

namespace Njasm\Container\Definition;

use Njasm\Container\Definition\Definition;

interface DefinitionsMapInterface
{
    public function has($key);
    public function add(Definition $definition);
    public function get($key);
}

