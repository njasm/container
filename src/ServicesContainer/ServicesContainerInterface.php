<?php

namespace Njasm\ServicesContainer;

use Njasm\ServicesContainer\ServicesProviderInterface;

interface ServicesContainerInterface 
{
    /**
     * Register a new service in the container.
     * 
     * @param   string    $service  the service key
     * @param   closure   $value    the closure that will build and return the object
     * @return  ServicesContainer
     */    
    public function set($service, \Closure $function);
    
    /**
     * Register a new service as a singleton instance in the container.
     * 
     * @param   string    $service  the service key
     * @param   closure   $value    the closure that will build and return the object
     * @return  ServicesContainer
     */    
    public function singleton($service, \Closure $function);
    
    /**
     * Registers a/other container into the services providers storage.
     * 
     * @param   ServicesProviderInterface   $provider   the container
     * @return  ServicesContainer
     */    
    public function provider(ServicesProviderInterface $provider);
}
