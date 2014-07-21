<?php

namespace Njasm\Container\Factory;

use Njasm\Container\Definition\Request;

class ProviderFactory extends AbstractFactory
{
    public function build(Request $request)
    {
        $key        = $request->getKey();
        $providers  = $request->getProviders();
        
        foreach ($providers as $provider) {
            if ($provider->has($key)) {
                return $provider->get($key);
            }
        }
    }
}
