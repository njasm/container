<?php

namespace Njasm\Container\Definition\Service;

class FinderService 
{
    static $finder;
    
    protected static function init()
    {
        $localFinder = new LocalFinder();
        $localFinder->append(new ProvidersFinder());
        self::$finder = $localFinder;
    }
    
    protected static function getFinder()
    {
        if (!isset(self::$finder)) {
            self::init();
        }
        
        return self::$finder;
    }
    
    public static function isRegistered(Request $request)
    {
        return self::getFinder()->has($request);
    }
}
