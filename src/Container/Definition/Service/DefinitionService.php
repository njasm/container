<?php

namespace Njasm\Container\Definition\Service;

use \Njasm\Container\Definition\AbstractDefinition;
use \Njasm\Container\Definition\Builder\BuilderInterface;
use Njasm\Container\Definition\Finder\AbstractFinder;
use Njasm\Container\Definition\Finder\FindRequest;

class DefinitionService
{
    protected $finder;
    protected $builders;
    
    public function has(FindRequest $request)
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
    
    public function assemble($key, $value)
    {
        if ($value instanceof \Closure) {
            return new \Njasm\Container\Definition\FactoryDefinition($key, $value);
        }elseif (is_object($value)) {
            return new \Njasm\Container\Definition\ObjectDefinition($key, $value);
        }elseif (is_scalar($value)) {
            return new \Njasm\Container\Definition\PrimitiveDefinition($key, $value);
        }
        
        throw new \OutOfBoundsException("Unknown definition type.");
    }
    
    public function build(AbstractDefinition $definition)
    {
        $type = $definition->getType();
        
        if (!isset($this->builders[$type])) {
            throw new \OutOfBoundsException("There is not registered builder for this type of definition.");
        }
        
        return $this->builders[$type]->execute($definition->getDefinition());
    }
    
    public function appendBuilder($definitionType, BuilderInterface $builder)
    {
        $this->builders[$definitionType] = $builder;
    }
}
