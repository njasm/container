<?php

namespace Njasm\ServicesContainer;

use Njasm\ServicesContainer\Exception\ServiceNotRegisteredException;
use Njasm\ServicesContainer\ServicesContainerInterface;
use Njasm\ServicesContainer\ServicesProviderInterface;

class ServicesContainer implements ServicesContainerInterface, ServicesProviderInterface
{
    private $map;
    private $singletons;
    private $instances;
    private $providers;
    
    public function __construct()
    {
        $this->map = array();
        $this->providers = new \SplObjectStorage();
    }
    
    /**
     * Check if service is registered.
     * 
     * @param   string  $service    The service to check
     * @return  boolean
     */    
    public function has($service)
    {
        if (isset($this->map[$service])) {
            return true;
        }
        
        return $this->providerHas($service);
    }
    
    /**
     * Check if any sub container have the service registered.
     * 
     * @param   string  $service    the service to look for
     * @return  boolean
     */
    protected function providerHas($service)
    {
        foreach ($this->providers as $serviceProvider) {
            if ($serviceProvider->has($service)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Register a new service in the container.
     * 
     * @param   string      $service  the service key
     * @param   \Closure    $value    the closure that will build and return the object
     * @return  ServicesContainer
     */
    public function set($service, \Closure $function)
    {
        $this->map[$service] = $function;
        
        return $this;
    }
    
    /**
     * Register a new service as a singleton instance in the container.
     * 
     * @param   string      $service  the service key
     * @param   \Closure    $value    the closure that will build and return the object
     * @return  ServicesContainer
     */
    public function singleton($service, \Closure $function)
    {
        $this->set($service, $function);
        $this->singletons[$service] = true;
        
        return $this;
    }
    
    /**
     * Registers a/other container into the services providers storage.
     * 
     * @param   ServicesProviderInterface   $provider   the container
     * @return  ServicesContainer
     */
    public function provider(ServicesProviderInterface $provider)
    {
        $this->providers->attach($provider);
        
        return $this;        
    }
    
    /**
     * Returns the instanciated object
     * 
     * @param   string  $service    the service to instanciate
     * @return  object
     * 
     * @throws  ServiceNotRegisteredException
     */
    public function get($service)
    {
        if (!$this->has($service)) {
            throw new ServiceNotRegisteredException();
        }
        
        return $this->getRegistered($service);
    }
    
    /**
     * Returns the instanciated service
     * 
     * @param   string  $service    the service to instanciate
     * @return  object
     */
    protected function getRegistered($service)
    {
        if ($this->isSingleton($service)) {
            return $this->getSingleton($service);
        }
        
        if (isset($this->map[$service])) {
            return $this->map[$service]();
        }
        
        return $this->getFromProviders($service);
    }
    
    /**
     * Returns the service registered as singleton instance
     * 
     * @param   string  $service    the singleton service to instanciate
     * @return  object
     */
    protected function getSingleton($service)
    {
        if (isset($this->instances[$service]) === true) {
            return $this->instances[$service];
        } 
        
        $this->instances[$service] = $this->map[$service]();
        
        return $this->instances[$service];
    }
    
    /**
     * Checks if a service is registered as singleton
     * 
     * @param   string  $service    the service to check
     * @return  boolean
     */
    protected function isSingleton($service)
    {
        return isset($this->singletons[$service]);
    }
    
    /**
     * Get from providers the instanciated service
     * 
     * @param   string  $service    the service to instanciate
     * @return  object
     */
    protected function getFromProviders($service)
    {
        $object = null;
        foreach ($this->providers as $serviceProvider) {
            if ($serviceProvider->has($service)) {
                $object = $serviceProvider->get($service);
                break;
            }                  
        }
        
        return $object;
    }
}
