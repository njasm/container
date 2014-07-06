<?php

namespace Njasm\Container\Builder\Service;

use \Njasm\Container\Definition\Types;
use \Njasm\Container\Definition\AbstractDefinition;

class BuilderService
{
    static $builders;
    
    protected static function init()
    {
        self::$builders[Types::SINGLETON] = new \Njasm\Container\Builder\SingletonCommand();
        self::$builders[Types::FACTORY] = new \Njasm\Container\Builder\FactoryCommand();
        self::$builders[Types::PRIMITIVE] = new \Njasm\Container\Builder\PrimitiveCommand();
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
