<?php

namespace Njasm\Container;

use Njasm\Container\Exception\NotFoundException;
use Njasm\Container\ServicesProviderInterface;
use Njasm\Container\Definition\AbstractDefinition;

use Njasm\Container\Definition\Types;
use Njasm\Container\Definition\Service\DefinitionFinderService;

class Container implements ServicesProviderInterface
{
    protected $map;    
    protected $providers;
    protected $registry;
    protected $singletons;
    

    public function __construct()
    {
        $this->map = new Definition\DefinitionMap();
        $this->providers = new \SplObjectStorage();
        
        // register Container
        $this->set(new Definition\ObjectDefinition('Njasm\Container\Container', $this));       
    }
    
    /**
     * Check if service is registered with this container.
     * 
     * @param   string  $key    The service to check
     * @return  boolean
     */    
    public function has($key)
    {
        return \Njasm\Container\Definition\Service\FinderService::isRegistered(
            new Definition\Service\Request($key, $this->map, $this->providers)
        );
    }
    
    /**
     * Register a new service in the container
     * 
     * @param   DefinitionInterface $definition    the service key
     * @return  Container
     */
    public function set(AbstractDefinition $definition)
    {
        $this->map[$definition->getKey()] = $definition;
        return $this;
    }
    
    /**
     * Register a new service as a singleton instance in the container
     * 
     * @param   string      $definition    the service key
     * @return  Container
     */
    public function singleton(AbstractDefinition $definition)
    {
        $this->set($definition);
        $key = $definition->getKey();
        $this->singletons[$key] = true;
        
        if (is_object($definition->getDefinition()) && !$definition->getDefinition() instanceof \Closure) {
            $this->registry[$key] = $definition->getDefinition();
        }
        
        return $this;
    }
    
    /**
     * Registers a/other container into the services providers storage
     * 
     * @param   ServicesProviderInterface   $provider   the container
     * @return  Container
     */
    public function provider(ServicesProviderInterface $provider)
    {
        $this->providers->attach($provider);
        return $this;        
    }   

    /**
     * Returns the instantiated object
     * 
     * @param   string  $definition    the service to instantiate
     * @return  mixed
     */
    public function get($key)
    {   
        if (isset($this->registry[$key])) {
            return $this->registry[$key];
        }
        
        if (isset($this->map[$key])) {
            $definition = $this->map[$key];
            $type = $definition->getType();

            $object = \Njasm\Container\Builder\Service\BuilderService::build($type, $definition);
             
            return $this->isSingleton($key) ? $this->registry[$key] = $object : $object;
        }
        
        return $this->getFromProviders($key);
    }
    
    /**
     * Removes a service from the storage. This will NOT remove services from other nested providers
     * 
     * @param   string  $definition
     * @return  bool    true if service removed, false otherwise
     */
    public function remove($key)
    {
        if(isset($this->map[$key])) {
            unset($this->map[$key]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Calls the storage reset
     * 
     * @return  bool    true on storage complete reset.
     */ 
    public function reset()
    {
        $this->providers = new \SplObjectStorage();
        $this->map = new Definition\DefinitionMap();
        $this->registry = array();
        $this->singletons = array();
        return true;
    }
    
    protected function isSingleton($key)
    {
        return isset($this->singletons[$key]);
    }
    
    protected function getFromProviders($key)
    {
        foreach($this->providers as $provider) {
            if ($provider->has($key)) {
                return $provider->get($key);
            }
        }
        
        throw new NotFoundException();
    }
}
