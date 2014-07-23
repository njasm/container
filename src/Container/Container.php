<?php

namespace Njasm\Container;

use Njasm\Container\Exception\NotFoundException;
use Njasm\Container\ServicesProviderInterface;
use Njasm\Container\Definition\DefinitionMap;
use Njasm\Container\Definition\Service\DefinitionService;
use Njasm\Container\Definition\Finder\LocalFinder;
use Njasm\Container\Definition\Finder\ProvidersFinder;
use Njasm\Container\Definition\Request;

class Container implements ServicesProviderInterface
{
    protected $map;    
    protected $providers;
    protected $registry;
    protected $singletons;
    protected $service;

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Initializes the Container.
     * 
     * @return void
     */
    protected function initialize()
    {
        $this->providers = new \SplObjectStorage();
        $this->map = new DefinitionMap();
        $this->registry = array();
        $this->singletons = array();
        
        $this->service = new DefinitionService();
        
        // setup finders chain
        $localFinder = new LocalFinder();
        $providersFinder = new ProvidersFinder();
        $this->service->appendFinder($localFinder);
        $this->service->appendFinder($providersFinder);
        
        // register Container
        $this->set('Njasm\Container\Container', $this);  
    }
    
    /**
     * Check if service is registered.
     * 
     * @param   string      $key
     * @return  boolean
     */    
    public function has($key)
    {
        return $this->service->has(new Request($key, $this->map, $this->providers));
    }
    
    /**
     * Register a new service in the container.
     * 
     * @param   string      $key
     * @param   mixed       $concrete
     * @return  Container
     */
    public function set($key, $concrete)
    {
        $definition = $this->service->assemble($key, $concrete);
        $this->map->add($definition);
        
        return $this;
    }
    
    /**
     * Registers service as a singleton instance in the container.
     * 
     * @param   string      $key
     * @param   mixed       $concrete
     * @return  Container
     */
    public function singleton($key, $concrete)
    {
        $this->set($key, $concrete);
        $this->singletons[$key] = true;
        
        return $this;
    }
    
    /**
     * Registers another services provider container.
     * 
     * @param   ServicesProviderInterface   $provider
     * @return  Container
     */
    public function provider(ServicesProviderInterface $provider)
    {
        $this->providers->attach($provider);
        
        return $this;        
    }   

    /**
     * Returns the service.
     * 
     * @param   string  $key
     * @return  mixed
     * 
     * @throws  NotFoundException
     */
    public function get($key)
    {   
        if (isset($this->registry[$key])) {
            return $this->registry[$key];
        }
        
        if (!$this->has($key)) {
            throw new NotFoundException();
        }
        
        $request = new Request($key, $this->map, $this->providers);
        $returnValue = $this->service->build($request);

        return $this->isSingleton($key) ? $this->registry[$key] = $returnValue : $returnValue;        
    }
    
    /**
     * Removes a service from the container. 
     * This will NOT remove services from other nested providers.
     * 
     * @param   string  $key
     * @return  boolean
     */
    public function remove($key)
    {
        if(isset($this->map[$key])) {
            unset($this->map[$key]);
            unset($this->registry[$key]);
            unset($this->singletons[$key]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Reset container settings.
     * 
     * @return  void
     */ 
    public function reset()
    {
        $this->initialize();
    }
    
    /**
     * Check if service is registered as a singleton.
     * 
     * @param   string  $key
     * @return  boolean
     */
    protected function isSingleton($key)
    {
        return isset($this->singletons[$key]);
    }
}
