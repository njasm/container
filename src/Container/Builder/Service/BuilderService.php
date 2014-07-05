<?php

namespace Njasm\Container\Builder\Service;

use \Njasm\Container\Definition\Types;

class BuilderService
{
    static $builders;
    
    protected static function init()
    {
        self::$builders[Types::SINGLETON] = new \Njasm\Container\Builder\SingletonBuilder();
        self::$builders[Types::FACTORY] = new \Njasm\Container\Builder\FactoryBuilder();
        self::$builders[Types::PRIMITIVE] = new \Njasm\Container\Builder\PrimitiveBuilder();
    }
    
    protected static function getBuilders()
    {
        if (!isset(self::$builders)) {
            self::init();
        }
        
        return static::$builders;
    }
    
    public static function build($type, $definition)
    {
        $builders = self::getBuilders();
        
        return $builders[$type]->build($definition);
    }
}
