<?php

namespace Njasm\Container\Definition\Service;

class FinderService 
{
    static $finder;
    
    protected function init()
    {
        $localFinder = new LocalFinder();
        $providersFinder = new ProvidersFinder();
        $localFinder->append($providersFinder);
        static::$finder = $localFinder;
    }
    
    protected static function getFinder()
    {
        if (!isset(static::$finder)) {
            self::init();
        }
        
        return static::$finder;
    }
    
    public static function isRegistered(Request $request)
    {
        return static::getFinder()->has($request);
    }
}
