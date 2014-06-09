<?php

namespace Njasm\ServicesContainer;

use Njasm\ServicesContainer\Exception\ServiceNotRegisteredException;
use Njasm\ServicesContainer\ServicesProviderInterface;

class ServicesContainer implements \Njasm\ServicesContainer\ServicesProviderInterface
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
     * @param   string    $service  the service key
     * @param   closure   $value    the closure that will build and return the object
     * @return  ServicesContainer
     */
    public function set($service, \Closure $value)
    {
        $this->map[$service] = $value;
        
        return $this;
    }
    
    /**
     * Register a new service as a singleton instance in the container.
     * 
     * @param   string    $service  the service key
     * @param   closure   $value    the closure that will build and return the object
     * @return  ServicesContainer
     */
    public function singleton($service, \Closure $value)
    {
        $this->set($service, $value);
        $this->singletons[$service] = true;
        
        return $this;
    }
    
    /**
     * Registers another container into the tree
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
    private function getRegistered($service)
    {
        if ($this->isSingleton($service)) {
            return $this->getSingleton($service);
        }
        
        if (isset($this->map[$service])) {
            return $this->map[$service]();
        }
        
        return $this->providerGet($service);
    }
    
    /**
     * Returns the service registered as singleton instance
     * 
     * @param   string  $service    the singleton service to instanciate
     * @return  object
     */
    private function getSingleton($service)
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
    private function isSingleton($service)
    {
        return isset($this->singletons[$service]);
    }
    
    /**
     * Get from providers the instanciated service
     * 
     * @param   string  $service    the service to instanciate
     * @return  object
     */
    private function providerGet($service)
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
