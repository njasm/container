<?php

namespace Njasm\Container\Definition\Service;

use Njasm\Container\Definition\Definition,
    Njasm\Container\Definition\DefinitionType,
    Njasm\Container\Definition\Finder\AbstractFinder,
    Njasm\Container\Definition\Service\Request;

class DefinitionService
{
    protected $finder;

    public function has(Request $request)
    {
        return $this->localHas($request) || $this->providersHas($request);
    }
    
    protected function localHas(Request $request)
    {
        $finder = new \Njasm\Container\Definition\Finder\LocalFinder();
        return $finder->has($request);
    }
    
    protected function providersHas(Request $request)
    {
        $finder = new \Njasm\Container\Definition\Finder\ProvidersFinder();
        return $finder->has($request);
    }
    
    public function appendFinder(AbstractFinder $finder)
    {
        if(isset($this->finder)) {
            $this->finder->append($finder);

            return;
        }

        $this->finder = $finder;
    }
    
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
     * @todo    Reflection ?
     * @param   Request     $request
     * @return  mixed
     */
    public function build(Request $request)
    {
        $factory = null;
        
        // check local
        if ($this->localHas($request)) {
            $factory = new \Njasm\Container\Factory\LocalFactory();
        }
        
        // check in nested providers
        if ($factory === null && $this->providersHas($request)) {
            $factory = new \Njasm\Container\Factory\ProviderFactory();
        }
        
        // TODO: try to bail-out client call with reflection.
        if ($factory === null) {
            $factory = new \Njasm\Container\Factory\LocalFactory();
            // build new definition to inject into the definitionsMap
            // if the ReflectionBuilder is able to instanciate an object we will register that as the concrete
            // of the Definition, else we will remove the definition and re-throw the Exception.
            $def = new Definition($request->getKey(), null, new DefinitionType(DefinitionType::REFLECTION));
            $definitionsMap = $request->getDefinitionsMap();
            $definitionsMap->add($def);
        }
        
        return $factory->build($request);  
    }
}
