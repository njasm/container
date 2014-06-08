<?php

namespace Njasm\ServicesContainer;

use Njasm\ServicesContainer\ServicesProviderInterface;

class ServicesContainer implements \Njasm\ServicesContainer\ServicesProviderInterface
{
    private $map;
    private $singletons;
    private $instances;
    private $services;
    
    public function __construct()
    {
        $this->map = array();
        $this->services = new \SplObjectStorage();
    }
    
    /**
     * Check if service is registered.
     * 
     * @param   string  $service The service to check
     * @return  boolean true or false
     */    
    public function has($service)
    {
        if (isset($this->map[$service])) {
            return true;
        }
        
        foreach ($this->services as $serviceProvider) {
            if ($serviceProvider->has($service)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Register a new service in the container.
     * 
     * @param string    $service    the service key
     * @param closure   $value      the closure that will build and return the object
     */
    public function set($service, callable $value)
    {
        $this->map[$service] = $value;
        
        return $this;
    }
    
    /**
     * Register a new service as a singleton instance in the container.
     * 
     * @param   string    $service    the service key
     * @param   closure   $value      the closure that will build and return the object
     * @return  ServicesContainer
     */
    public function singleton($service, callable $value)
    {
        $this->set($service, $value);
        $this->singletons[$service] = true;
        
        return $this;
    }
    
    /**
     * Register another container into the tree
     * 
     * @param   ServiceProviderInterface $provider  the container
     * @return  ServicesContainer
     */
    public function service(ServicesProviderInterface $provider)
    {
        $this->services->attach($provider);
        
        return $this;        
    }
    
    /**
     * Returns the instanciated object
     * 
     * @param   string $service
     * @return  Object
     * @throws  ServiceNotRegisteredException
     */
    public function get($service)
    {
        if (!$this->has($service)) {
            throw new \Njasm\ServicesContainer\Exception\ServiceNotRegisteredException();
        }
        
        return $this->getRegistered($service);
    }
    
    
    private function getRegistered($service)
    {
        if ($this->isSingleton($service)) {
            return $this->getSingleton($service);
        }
        
        if (isset($this->map[$service])) {
            return $this->map[$service]();
        }
        
        return $this->getFromProvider($service);
    }
    
    /**
     * Returns the service registered as singleton instance
     * 
     * @param   string  $service    the singleton service
     * @return  object  the singleton service
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
     * @param   string  $service    the service
     * @return  boolean true or false
     */
    private function isSingleton($service)
    {
        return isset($this->singletons[$service]);
    }
    
    /**
     * Get from providers the instanciated service
     * 
     * @param   string  $service    the service to instanciate
     * @return  object  the instanciated service 
     */
    private function getFromProvider($service)
    {
        foreach ($this->services as $serviceProvider) {
            if ($serviceProvider->has($service)) {
                $object = $serviceProvider->get($service);
                break;
            }                  
        }
        
        return $object;
    }
}
