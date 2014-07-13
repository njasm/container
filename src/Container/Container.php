<?php

namespace Njasm\Container;

use Njasm\Container\Exception\NotFoundException;
use Njasm\Container\ServicesProviderInterface;
use Njasm\Container\Definition\AbstractDefinition;

use Njasm\Container\Definition\DefinitionType;

use Njasm\Container\Definition\Service\DefinitionService;

use Njasm\Container\Definition\Finder\LocalFinder;
use Njasm\Container\Definition\Finder\ProvidersFinder;
use Njasm\Container\Definition\Finder\FindRequest;

use Njasm\Container\Definition\Builder\SingletonBuilder;
use Njasm\Container\Definition\Builder\FactoryBuilder;
use Njasm\Container\Definition\Builder\PrimitiveBuilder;

class Container implements ServicesProviderInterface
{
    protected $map;    
    protected $providers;
    protected $registry;
    protected $singletons;
    
    protected $definitionService;
    protected $builder;

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
        $this->map = new Definition\DefinitionMap();
        $this->registry = array();
        $this->singletons = array();
        
        // instanciate Definition Service
        $this->definitionService = new DefinitionService();
        
        // setup finders chain
        $localFinder = new LocalFinder();
        $providersFinder = new ProvidersFinder();
        $this->definitionService->appendFinder($localFinder);
        $this->definitionService->appendFinder($providersFinder);
        
        // setup builders commands  
        $this->definitionService->appendBuilder(DefinitionType::SINGLETON, new SingletonBuilder());
        $this->definitionService->appendBuilder(DefinitionType::FACTORY, new FactoryBuilder());
        $this->definitionService->appendBuilder(DefinitionType::PRIMITIVE, new PrimitiveBuilder());
        
        // register Container
        $this->set('Njasm\Container\Container', $this);  
    }
    
    /**
     * Check if service is registered with this container.
     * 
     * @param   string  $key    The service to check
     * @return  boolean
     */    
    public function has($key)
    {
        return $this->definitionService->has(new FindRequest($key, $this->map, $this->providers));
    }
    
    /**
     * Register a new service in the container
     * 
     * @param   DefinitionInterface $definition    the service key
     * @return  Container
     */
    public function set($key, $value)
    {
        $definition = $this->definitionService->assemble($key, $value);
        $this->map[$definition->getKey()] = $definition;
        
        if ($definition->getType() === DefinitionType::SINGLETON) {
            $this->registry[$key] = $definition->getDefinition();
        }
        
        return $this;
    }
    
    /**
     * Register a new service as a singleton instance in the container
     * 
     * @param   string      $definition    the service key
     * @return  Container
     */
    public function singleton($key, $value)
    {
        $this->set($key, $value);
        $this->singletons[$key] = true;
        
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
            $returnValue = $this->definitionService->build($definition);

            return $this->isSingleton($key) ? $this->registry[$key] = $returnValue : $returnValue;
        }
        
        return $this->getFromProviders($key);
    }
    
    /**
     * Removes a service from the storage. This will NOT remove services from other nested providers
     * 
     * @param   string  $key
     * @return  bool    true if service removed, false otherwise
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
     * Reset all container settings.
     * 
     * @return  bool    true
     */ 
    public function reset()
    {
        $this->initialize();
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
