<?php

namespace Njasm\Container\Definition\Finder;

class ProvidersFinder extends AbstractFinder
{
    protected function handle(FindRequest $request)
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
