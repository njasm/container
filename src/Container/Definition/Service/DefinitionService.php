<?php

namespace Njasm\Container\Definition\Service;

use Njasm\Container\Definition\Definition,
    Njasm\Container\Definition\DefinitionType,
    Njasm\Container\Definition\Finder\AbstractFinder,
    Njasm\Container\Definition\Finder\LocalFinder,
    Njasm\Container\Definition\Finder\ProvidersFinder,
    Njasm\Container\Definition\Service\Request,
    Njasm\Container\Factory\LocalFactory,
    Njasm\Container\Factory\ProviderFactory;

class DefinitionService
{
    protected $finder;

    public function has(Request $request)
    {
        return $this->localHas($request) || $this->providersHas($request);
    }
    
    protected function localHas(Request $request)
    {
        $finder = new LocalFinder();
        return $finder->has($request);
    }
    
    protected function providersHas(Request $request)
    {
        $finder = new ProvidersFinder();
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
        // check local
        if ($this->localHas($request)) {
            $factory = new LocalFactory();
            return $factory->build($request);  
        }
        
        // check in nested providers
        if ($this->providersHas($request)) {
            $factory = new ProviderFactory();
            return $factory->build($request);  
        }
        
        // try to bail-out client call with reflection.
        // if we're able to resolve all dependencies, we'll assemble a new 
        // definition with the returned value for future use.
        $factory = new LocalFactory();
        $key = (string) $request->getKey();

        // temporary definition
        $def = new Definition($key, null, new DefinitionType(DefinitionType::REFLECTION));
        $request->getDefinitionsMap()->add($def);

        $returnValue = $factory->build($request);
        $container = $request->getContainer()->singleton($key,$returnValue);
        
        return $returnValue;  
    }
}
