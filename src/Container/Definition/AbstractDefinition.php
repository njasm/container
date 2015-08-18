<?php

namespace Njasm\Container\Definition;

use Njasm\Container\ServicesProviderInterface;

abstract class AbstractDefinition
{
    /** @var string */
    protected $key;

    /** @var Container */
    protected $container;

    public function __construct($key, ServicesProviderInterface $container)
    {
        $this->container = $container;
        $this->key = $key;
    }

    abstract public function build(array $constructor = array(), array $properties = array(), array $methods = array());
}
