<?php

namespace Njasm\ServicesContainer\Storage;

use Njasm\ServicesContainer\Storage\StorageInterface;
use Njasm\ServicesContainer\ServicesProviderInterface;
use Njasm\ServicesContainer\Exception\ServiceNotRegisteredException;

class InMemoryStorage implements StorageInterface
{
    private $services;
    private $singletons;
    private $instances;
    private $providers;
    
    public function __construct()
    {
        $this->services = array();
        $this->providers = new \SplObjectStorage();
    }
    
    /**
     * Check if service is registered
     * 
     * @param   string      $service    The service to check
     * @return  boolean
     */    
    public function has($service)
    {
        if (array_key_exists($service, $this->services)) {
            return true;
        }
        
        return $this->providerHas($service);
    }
    
    /**
     * Register a new service in the container
     * 
     * @param   string      $service    the service key
     * @param   mixed       $value      the actual service
     * @return  ServicesContainer
     */
    public function set($service, $value)
    {
        $this->services[$service] = $value;
        return $this;
    }
    
    /**
     * Register a new service as a singleton instance in the container
     * 
     * @param   string              $service    the service key
     * @param   closure|object      $value      the actual service
     * @return  ServicesContainer
     */
    public function singleton($service, $value)
    {
        if ($value instanceof \Closure || is_object($value)) {
            $this->set($service, $value);
            $this->singletons[$service] = true;   
            return $this;
        }
    }
    
    
    /**
     * Registers a/other container into the services providers storage
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
     * Returns the instantiated object
     * 
     * @param   string  $service    the service to instantiate
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
     * Returns the instantiated service
     * 
     * @param   string  $service    the service to instantiate
     * @return  object
     */
    protected function getRegistered($service)
    {
        if ($this->isSingleton($service)) {
            return $this->getSingleton($service);
        }
        
        if (isset($this->services[$service])) {
            if ($this->services[$service] instanceof \Closure) {
                return $this->services[$service]();
            }
            
            // primitives
            return $this->services[$service];
        }
        
        return $this->getFromProviders($service);
    }
    
    /**
     * Returns the service registered as singleton instance
     * 
     * @param   string  $service    the singleton service to instantiate
     * @return  object
     */
    protected function getSingleton($service)
    {
        if (isset($this->instances[$service]) === true) {
            return $this->instances[$service];
        } 
        
        $this->instances[$service] = $this->services[$service]();
        
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
     * Check if any sub container have the service registered
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
     * Get from providers the instantiated service
     * 
     * @param   string  $service    the service to instantiate
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
    
    public function remove($service)
    {
        $has = $this->has($service);
        if ($has && $this->isSingleton($service)) {
            unset($this->singletons[$service]);
            unset($this->instances[$service]);
            return true;
        } elseif ($has) {
            unset($this->services[$service]);
            return true;
        }
        
        return false;
    }
    
    public function reset()
    {
        $this->instances = array();
        $this->services = array();
        $this->singletons = array();
        $this->providers = new \SplObjectStorage();
        return true;
    }
}
