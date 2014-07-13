<?php

namespace Njasm\Container\Builder\Service;

use \Njasm\Container\Definition\DefinitionType;
use \Njasm\Container\Definition\AbstractDefinition;

class BuilderService
{
    static $builders;
    
    protected static function init()
    {
        self::$builders[DefinitionType::SINGLETON] = new \Njasm\Container\Builder\SingletonBuilder();
        self::$builders[DefinitionType::FACTORY] = new \Njasm\Container\Builder\FactoryBuilder();
        self::$builders[DefinitionType::PRIMITIVE] = new \Njasm\Container\Builder\PrimitiveBuilder();
    }
    
    protected static function getBuilders()
    {
        if (!isset(self::$builders)) {
            self::init();
        }
        
        return self::$builders;
    }
    
    public static function build(AbstractDefinition $definition)
    {
        $builders = self::getBuilders();
        if (!array_key_exists($definition->getType(), $builders)) {
            throw new \OutOfBoundsException("No builder registered for type: {$definition->getType()}");
        }
        
        return $builders[$definition->getType()]->execute($definition);
    }
    
    //TODO: AppendCommand() method
}
