<?php

namespace Njasm\Container\Builder\Service;

use \Njasm\Container\Definition\Types;

class BuilderService
{
    static $builders;
    
    protected function init()
    {
        // setup factories for returning, building definitions
        static::$builders[Types::SINGLETON] = new \Njasm\Container\Builder\SingletonBuilder();
        static::$builders[Types::FACTORY] = new \Njasm\Container\Builder\FactoryBuilder();
        static::$builders[Types::PRIMITIVE] = new \Njasm\Container\Builder\PrimitiveBuilder();
    }
    
    protected static function getBuilders()
    {
        if (!isset(static::$builders)) {
            self::init();
        }
        
        return static::$builders;
    }
    
    public static function build($type, $definition)
    {
        $builders = static::getBuilders();
        
        return $builders[$type]->build($definition);
    }
}
