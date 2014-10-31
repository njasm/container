<?php

namespace Njasm\Container\Factory;

use Njasm\Container\Definition\Service\Request;
use \Njasm\Container\Definition\DefinitionType;
use Njasm\Container\Definition\Builder\AliasBuilder;
use Njasm\Container\Definition\Builder\PrimitiveBuilder;
use Njasm\Container\Definition\Builder\ObjectBuilder;
use Njasm\Container\Definition\Builder\ClosureBuilder;
use Njasm\Container\Definition\Builder\ReflectionBuilder;

class LocalFactory implements FactoryInterface
{
    protected $builders = array();

    public function __construct()
    {
        /*
        $this->builders[DefinitionType::ALIAS] = 'Njasm\Container\Definition\Builder\AliasBuilder';
        $this->builders[DefinitionType::PRIMITIVE] = 'Njasm\Container\Definition\Builder\PrimitiveBuilder';
        $this->builders[DefinitionType::OBJECT] = 'Njasm\Container\Definition\Builder\ObjectBuilder';
        $this->builders[DefinitionType::CLOSURE] = 'Njasm\Container\Definition\Builder\ClosureBuilder';
        $this->builders[DefinitionType::REFLECTION] = 'Njasm\Container\Definition\Builder\ReflectionBuilder';
        */
    }

    public function build(Request $request)
    {
        $key                = $request->getKey();
        $definitionsMap     = $request->getDefinitionsMap();
        $definition         = $definitionsMap->get($key);
        $builder            = $this->getBuilder($definition->getType());
        //$builder            = $this->getNewBuilder($definition->getType());

        return $builder->execute($request);       
    }

    protected function getNewBuilder($builderType)
    {
        if (!array_key_exists($builderType, $this->builders)) {
            throw new \OutOfBoundsException("No available factory to build the requested service.");
        }

        return new $this->builders[$builderType];
    }

    protected function getBuilder($builderType)
    {
        switch ($builderType) {
            case DefinitionType::PRIMITIVE:
                
                return new PrimitiveBuilder();
                
            case DefinitionType::OBJECT:
                
                return new ObjectBuilder();
                
            case DefinitionType::CLOSURE:
                
                return new ClosureBuilder();
                
            case DefinitionType::REFLECTION:
                
                return new ReflectionBuilder();
                
            case DefinitionType::ALIAS:
                
                return new AliasBuilder();
                
            default:
                
                throw new \OutOfBoundsException("No available factory to build the requested service.");
        }         
    }
}

