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
        self::$finder = $localFinder;
    }
    
    protected static function getFinder()
    {
        if (!isset(self::$finder)) {
            self::init();
        }
        
        return static::$finder;
    }
    
    public static function isRegistered(Request $request)
    {
        return self::getFinder()->has($request);
    }
}
