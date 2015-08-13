<?php

namespace Njasm\Container\Definition\Finder;

use Njasm\Container\Definition\Service\Request;

class DefinitionFinder
{
    public function has(Request $request)
    {
        if ($request->getDefinitionsMap()->has($request->getKey())) {
            return true;
        }

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
