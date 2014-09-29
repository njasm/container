<?php

namespace Njasm\Container\Adapter;

use Njasm\Container\ServicesProviderInterface;
use Joomla\DI\Container as JoomlaContainer;

class JoomlaAdapter implements ServicesProviderInterface
{
    protected $container;
    
    public function __construct(JoomlaContainer $container)
    {
        $this->container = $container;
    }
    
    public function get($id)
    {
        return $this->container->get($id);
    }

    public function has($id)
    {
        return $this->container->exists($id);
    }
}
