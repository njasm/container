<?php

namespace Njasm\Container\Definition;

use Njasm\Container\ServicesProviderInterface;

class ClosureDefinition extends AbstractDefinition
{
    /** @var \Closure */
    protected $concrete;

    /** @var array */
    protected $defaultConstructor = array();

    public function __construct($key, $concrete, ServicesProviderInterface $container, array $constructor = array()) {
        parent::__construct($key, $container);
        $this->concrete = $concrete;
        $this->defaultConstructor = $constructor;
    }

    public function build(array $constructor = array(), array $properties = array(), array $methods = array())
    {
        $constructor = !empty($constructor) ? $constructor : $this->defaultConstructor;

        return call_user_func_array($this->concrete, $constructor);
    }
}
