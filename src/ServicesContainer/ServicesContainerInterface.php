<?php

namespace Njasm\ServicesContainer;

use Njasm\ServicesContainer\ServicesProviderInterface;

interface ServicesContainerInterface 
{
    /**
     * Register a new service in the container.
     * 
     * @param   string      $service    the service key
     * @param   \Closure    $closure    the closure that will build and return the object
     * @return  ServicesContainer
     */    
    public function set($service, \Closure $closure);
    
    /**
     * Register a new service as a singleton instance in the container.
     * 
     * @param   string      $service    the service key
     * @param   \Closure    $closure    the closure that will build and return the object
     * @return  ServicesContainer
     */    
    public function singleton($service, \Closure $closure);
    
    /**
     * Registers a/other container into the services providers storage.
     * 
     * @param   ServicesProviderInterface   $provider   the container
     * @return  ServicesContainer
     */    
    public function provider(ServicesProviderInterface $provider);
}
