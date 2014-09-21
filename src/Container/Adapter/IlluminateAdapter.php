<?php

namespace Njasm\Container\Adapter;

use Njasm\Container\ServicesProviderInterface;
use Illuminate\Container\Container as IlluminateContainer;

class IlluminateAdapter implements ServicesProviderInterface
{
    protected $container;
    
    public function __construct(IlluminateContainer $container)
    {
        $this->container = $container;
    }
    
    public function get($id)
    {
        return $this->container->offsetGet($id);
    }

    public function has($id)
    {
        return $this->container->offsetExists($id);
    }
}
