<?php

namespace Njasm\Container\Definition;

use Njasm\Container\ServicesProviderInterface;
use Psr\Container\ContainerInterface;

class ClosureDefinition extends AbstractDefinition
{
    /** @var \Closure */
    protected $concrete;

    /** @var array */
    protected $defaultConstructor = array();

    public function __construct($key, $concrete, ContainerInterface $container, array $constructor = []) {
        parent::__construct($key, $container);
        $this->concrete = $concrete;
        $this->defaultConstructor = $constructor;
    }

    public function build(array $constructor = [], array $properties = [], array $methods = [])
    {
        $constructor = !empty($constructor) ? $constructor : $this->defaultConstructor;

        return call_user_func_array($this->concrete, $constructor);
    }
}
