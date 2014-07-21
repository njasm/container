<?php

namespace Njasm\Container\Factory;

use Njasm\Container\Definition\Request;

abstract class AbstractFactory
{
    abstract public function build(Request $request);
}
