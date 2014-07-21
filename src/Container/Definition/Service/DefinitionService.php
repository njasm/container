<?php

namespace Njasm\Container\Definition\Service;

use \Njasm\Container\Definition\AbstractDefinition;
use Njasm\Container\Definition\Finder\AbstractFinder;
use Njasm\Container\Definition\Request;

class DefinitionService
{
    protected $finder;
    protected $builders;
    
    public function has(Request $request)
    {      
        return $this->finder->has($request);        
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
        if ($concrete instanceof \Closure) {
            return new \Njasm\Container\Definition\ClosureDefinition($key, $concrete);
        }elseif (is_object($concrete)) {
            return new \Njasm\Container\Definition\ObjectDefinition($key, $concrete);
        }elseif (is_scalar($concrete)) {
            return new \Njasm\Container\Definition\PrimitiveDefinition($key, $concrete);
        }
        
        throw new \OutOfBoundsException("Unknown definition type.");
    }
    
    /**
     * @todo    Reflection ?
     * @param   \Njasm\Container\Definition\Request     $request
     * @return  mixed
     */
    public function build(Request $request)
    {
        $key        = $request->getKey();
        $map        = $request->getDefinitions();
        
        // check local
        if ($map->has($key)) {
            $factory = new \Njasm\Container\Factory\LocalFactory();
        }
        
        // check in nested providers
        $providerFinder = new \Njasm\Container\Definition\Finder\ProvidersFinder();
        if ($providerFinder->has($request)) {
            $factory = new \Njasm\Container\Factory\ProviderFactory();
        }
        
        return $factory->build($request);
        
    }
}
