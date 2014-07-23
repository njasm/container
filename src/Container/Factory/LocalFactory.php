<?php

namespace Njasm\Container\Factory;

use \Njasm\Container\Definition\Request;
use \Njasm\Container\Definition\DefinitionType;

use Njasm\Container\Definition\Builder\ClosureBuilder;
use Njasm\Container\Definition\Builder\ObjectBuilder;
use Njasm\Container\Definition\Builder\PrimitiveBuilder;
use Njasm\Container\Definition\Builder\ReflectionBuilder;

class LocalFactory extends AbstractFactory
{
    protected $definition;
    
    public function build(Request $request)
    {
        $key                = $request->getKey();
        $map                = $request->getDefinitions();
        $this->definition   = $map[$key];
        
        switch ($this->definition->getType()) {
            case DefinitionType::PRIMITIVE:
                
                $builder = new PrimitiveBuilder();
                return $builder->execute($this->definition->getConcrete());
                
            case DefinitionType::OBJECT:
                
                $builder = new ObjectBuilder();
                return $builder->execute($this->definition->getConcrete());
                
            case DefinitionType::CLOSURE:
                
                $builder = new ClosureBuilder();
                return $builder->execute($this->definition->getConcrete());
                
            case DefinitionType::REFLECTION:
                
                $builder = new ReflectionBuilder();
                return $builder->execute($this->definition->getKey());
                
            default:
                
                throw new \OutOfBoundsException("No available factory to build the requested service.");
        }        
    }
}

