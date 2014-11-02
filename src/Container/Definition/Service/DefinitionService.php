<?php

namespace Njasm\Container\Definition\Service;

use Njasm\Container\Definition\Definition;
use Njasm\Container\Definition\DefinitionType;
use Njasm\Container\Definition\Finder\LocalFinder;
use Njasm\Container\Definition\Finder\ProvidersFinder;
use Njasm\Container\Factory\LocalFactory;
use Njasm\Container\Factory\ProviderFactory;
use Njasm\Container\Exception\ContainerException;

class DefinitionService
{
    /**
     * @var array Service keys being built.
     */
    protected $buildingKeys = array();
    
    /**
     * Finds if a service is defined globally. Globally means, in this Container or in a nested Container.
     * 
     * @param \Njasm\Container\Definition\Service\Request $request
     * @return boolean
     */
    public function has(Request $request)
    {
        return $this->localHas($request) || $this->providersHas($request);
    }
    
    /**
     * Finds if a service is defined locally in Container.
     * 
     * @param \Njasm\Container\Definition\Service\Request $request
     * @return boolean
     */
    protected function localHas(Request $request)
    {
        $finder = new LocalFinder();
        return $finder->has($request);
    }
    
    /**
     * Finds if a service is defined in a nested Container.
     * 
     * @param \Njasm\Container\Definition\Service\Request $request
     * @return boolean
     */    
    protected function providersHas(Request $request)
    {
        $finder = new ProvidersFinder();
        return $finder->has($request);
    }
    
    /**
     * Assembles a Definition object based on the concrete value supplied.
     * 
     * @param   string      $key
     * @param   \Closure    $concrete
     * @return  \Njasm\Container\Definition\Definition
     * @throws  \OutOfBoundsException
     */
    public function assemble($key, $concrete)
    {
        $definitionType = null;
        
        if ($concrete instanceof \Closure) {
            $definitionType = new DefinitionType(DefinitionType::CLOSURE);
        }elseif (is_object($concrete)) {
            $definitionType = new DefinitionType(DefinitionType::OBJECT);
        }elseif (is_scalar($concrete)) {
            $definitionType = new DefinitionType(DefinitionType::PRIMITIVE);
        }
        
        if (!$definitionType instanceof DefinitionType) {
            throw new \OutOfBoundsException("Unknown definition type.");
        }
        
        return new Definition($key, $concrete, $definitionType);
    }
    
    /**
     * Assembles a Definition object of type Alias.
     * 
     * @param   string      $key
     * @param   string      $concrete
     * @return  \Njasm\Container\Definition\Definition
     */
    public function assembleAliasDefinition($key, $concrete)
    {
      return new Definition($key, $concrete, new DefinitionType(DefinitionType::ALIAS));  
    }

    /**
     * Assembles a Definition object of type Bind.
     *
     * @param   string      $key
     * @param   string      $concrete
     * @return  \Njasm\Container\Definition\Definition
     */
    public function assembleBindDefinition($key, $concrete)
    {
        return new Definition($key, $concrete, new DefinitionType(DefinitionType::REFLECTION));
    }

    /**
     * Build the requested service.
     * 
     * @param   Request     $request
     * @return  mixed
     */
    public function build(Request $request)
    {
        $key = (string) $request->getKey();
        $this->guardAgainstCircularDependency($key);
        $this->buildingKeys[$key] = true;
        $factory = $this->getFactory($request);
        $returnValue = $factory->build($request);
        unset($this->buildingKeys[$key]);

        return $returnValue;
    }

    /**
     * Guard against circular dependency when resolving dependencies.
     *
     * @param   string      $key
     * @return  void
     * @throws  ContainerException
     */
    protected function guardAgainstCircularDependency($key)
    {
        // circular dependency guard
        if (array_key_exists($key, $this->buildingKeys)) {
            throw new ContainerException("Circular Dependency detected for {$key}");
        }
    }

    /**
     * Return a factory to build the service.
     *
     * @param   Request     $request
     * @return  LocalFactory|ProviderFactory
     */
    protected function getFactory(Request $request)
    {
        // check local
        if ($this->localHas($request)) {
            return new LocalFactory();
        }

        // check in nested providers
        if ($this->providersHas($request)) {
            return new ProviderFactory();
        }

        // try to bail-out client called service.
        // We'll assemble a new reflection definition and will,
        // if class exists, try to resolve all dependencies
        // and instantiate the object if possible.
        $key = $request->getKey();
        $def = $this->assembleBindDefinition((string) $key, (string) $key);
        $request->getDefinitionsMap()->add($def);
        return new LocalFactory();
    }
}
