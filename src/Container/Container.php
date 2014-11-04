<?php

namespace Njasm\Container;

use Njasm\Container\Exception\NotFoundException;
use Njasm\Container\Definition\DefinitionsMap;
use Njasm\Container\Definition\Service\DefinitionService;
use Njasm\Container\Definition\Service\Request;

class Container implements ServicesProviderInterface
{
    protected $definitionsMap;    
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
        $this->providers = array();
        $this->definitionsMap = new DefinitionsMap();
        $this->registry = array();
        $this->singletons = array();
        
        $this->service = new DefinitionService();
        
        // register Container
        $this->set('Njasm\Container\Container', $this);
        $this->alias('Container', 'Njasm\Container\Container');
    }
    
    /**
     * Check if service is registered.
     * 
     * @param   string      $key
     * @return  boolean
     */    
    public function has($key)
    {
        return $this->service->has($this->getRequestObject($key));
    }
    
    /**
     * Register a new service in the container.
     * 
     * @param   string      $key
     * @param   mixed       $concrete
     * @param   array       $paramsToInject
     * @param   array       $methodsToCall
     * @return  Container
     */
    public function set($key, $concrete, array $paramsToInject = array(), array $methodsToCall = array())
    {
        $definition = $this->service->assemble($key, $concrete, $paramsToInject, $methodsToCall);
        $this->definitionsMap->add($definition);
        
        $definitionType = $definition->getType();
        
        if (
            $definitionType === Definition\DefinitionType::OBJECT 
            || $definitionType === Definition\DefinitionType::PRIMITIVE
        ) {
            $this->registerSingleton($key);
        }
        
        return $this;
    }

    /**
     * Register an alias to a service key.
     * 
     * @param   string      $alias
     * @param   string      $key
     * @return  Container
     */    
    public function alias($alias, $key)
    {
        $definition = $this->service->assembleAliasDefinition($alias, $key);
        $this->definitionsMap->add($definition);
        
        return $this;
    }

    /**
     * Bind a key to a FQCN accessible by autoload and instantiable.
     *
     * @param   string      $key
     * @param   string      $concrete  FQCN
     * @param   array       $paramsToInject
     * @param   array       $methodsToCall
     * @return  Container
     */
    public function bind($key, $concrete, array $paramsToInject = array(), array $methodsToCall = array())
    {
        $definition = $this->service->assembleBindDefinition($key, $concrete, $paramsToInject, $methodsToCall);
        $this->definitionsMap->add($definition);

        return $this;
    }

    /**
     * Bind a key to a FQCN accessible by autoload and instantiable, registering it as a Singleton.
     *
     * @param   string      $key
     * @param   string      $concrete   FQCN
     * @param   array       $paramsToInject
     * @param   array       $methodsToCall
     * @return  Container
     */
    public function bindSingleton($key, $concrete, array $paramsToInject = array(), array $methodsToCall = array())
    {
        $this->bind($key, $concrete, $paramsToInject, $methodsToCall);

        return $this->registerSingleton($key);
    }

    /**
     * Registers service as a singleton instance in the container.
     * 
     * @param   string      $key
     * @param   mixed       $concrete
     * @param   array   $paramsToInject
     * @param   array   $methodsToCall
     * @return  Container
     */
    public function singleton($key, $concrete, array $paramsToInject = array(), array $methodsToCall = array())
    {
        $this->set($key, $concrete, $paramsToInject, $methodsToCall);
        
        return $this->registerSingleton($key);
    }
    
    /**
     * Register a service key as singleton.
     * 
     * @param   string      $key
     * @return  Container
     */
    protected function registerSingleton($key)
    {
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
        $this->providers[] = $provider;

        return $this;        
    }   

    /**
     * Returns the service.
     * 
     * @param   string  $key
     * @param   array   $paramsToInject
     * @param   array   $methodsToCall
     * @return  mixed
     * 
     * @throws  NotFoundException
     */
    public function get($key, array $paramsToInject = array(), array $methodsToCall = array())
    {
        if (isset($this->registry[$key])) {
            return $this->registry[$key];
        }
        
        $request = $this->getRequestObject($key, $paramsToInject, $methodsToCall);
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
        if(isset($this->definitionsMap[$key])) {
            unset($this->definitionsMap[$key]);
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
    
    /**
     * Build a new Request value object.
     * 
     * @param   string      $key
     * @param   array       $paramsToInject
     * @param   array       $methodsToCall
     * @return  Request
     */
    protected function getRequestObject($key, array $paramsToInject = array(), array $methodsToCall = array())
    {
        return new Request($key, $this->definitionsMap, $this->providers, $this, $paramsToInject, $methodsToCall);
    }
}
