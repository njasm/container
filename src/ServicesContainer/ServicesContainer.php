<?php

namespace Njasm\ServicesContainer;

use Njasm\ServicesContainer\Exception\ServiceNotRegisteredException;
use Njasm\ServicesContainer\AbstractContainer;
use Njasm\ServicesContainer\ServicesProviderInterface;
use Njasm\ServicesContainer\Storage\StorageInterface;

class ServicesContainer extends AbstractContainer implements ServicesProviderInterface
{
    private $storage;
    
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }
    
    /**
     * Check if service is registered
     * 
     * @param   string  $service    The service to check
     * @return  boolean
     */    
    public function has($service)
    {
        return $this->storage->has($service);
    }
    
    /**
     * Register a new service in the container
     * 
     * @param   string      $service    the service key
     * @param   \Closure    $closure    the closure that will build and return the object
     * @return  ServicesContainer
     */
    public function set($service, $closure)
    {
        $this->storage->set($service, $closure);
        return $this;
    }
    
    /**
     * Register a new service as a singleton instance in the container
     * 
     * @param   string      $service    the service key
     * @param   \Closure    $closure    the closure that will build and return the object
     * @return  ServicesContainer
     */
    public function singleton($service, \Closure $closure)
    {
        $this->storage->singleton($service, $closure);
        return $this;
    }
    
    /**
     * Registers a/other container into the services providers storage
     * 
     * @param   ServicesProviderInterface   $provider   the container
     * @return  ServicesContainer
     */
    public function provider(ServicesProviderInterface $provider)
    {
        $this->storage->provider($provider);
        return $this;        
    }

    /**
     * Returns the instantiated object
     * 
     * @param   string  $service    the service to instantiate
     * @return  object
     * 
     * @throws  ServiceNotRegisteredException
     */
    public function get($service)
    {
        return $this->storage->get($service);
    }
}
