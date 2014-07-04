<?php

namespace Njasm\Container\Definition\Service;

use Njasm\Container\Definition\Service\Request;

class ProvidersFinder extends AbstractFinder
{
    protected function process(Request $request)
    {
        $key = $request->getKey();
        $providers = $request->getProviders();
        
        foreach ($providers as $provider) {
            if ($provider->has($key)) {
                return true;
            }
        }
        
        return false;
    }
}
