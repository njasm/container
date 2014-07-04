<?php

namespace Njasm\Container;

use Njasm\Container\ServicesProviderInterface;

abstract class AbstractContainer
{
    /**
     * Register a new service in the container.
     * 
     * @param   string      $service    the service key
     * @param   \Closure    $closure    the closure that will build and return the object
     * @return  Container
     */    
    abstract public function set($service, $closure);
    
    /**
     * Register a new service as a singleton instance in the container.
     * 
     * @param   string      $service    the service key
     * @param   \Closure    $closure    the closure that will build and return the object
     * @return  Container
     */    
    abstract public function singleton($service, \Closure $closure);
    
    /**
     * Registers a/other container into the services providers storage.
     * 
     * @param   ServicesProviderInterface   $provider   the container
     * @return  Container
     */    
    abstract public function provider(ServicesProviderInterface $provider);
}
