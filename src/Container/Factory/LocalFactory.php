<?php

namespace Njasm\Container\Factory;

use \Njasm\Container\Definition\Request;
use \Njasm\Container\Definition\DefinitionType;

use Njasm\Container\Definition\Builder\ClosureBuilder;
use Njasm\Container\Definition\Builder\ObjectBuilder;
use Njasm\Container\Definition\Builder\PrimitiveBuilder;

class LocalFactory extends AbstractFactory
{
    protected $definition;
    
    public function build(Request $request)
    {
        $key                = $request->getKey();
        $map                = $request->getDefinitions();
        $this->definition   = $map[$key];
        
        return $this->_build();

    }
    
    protected function _build()
    {
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
                
            default:
                
                throw new \OutOfBoundsException("No available factory to build the requested service.");
        }        
    }
}

