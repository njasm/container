<?php

namespace Njasm\Container\Definition\Finder;

use Njasm\Container\Definition\Service\Request;

class DefinitionFinder
{
    protected function localHas(Request $request)
    {
         return $request->getDefinitionsMap()->has($request->getKey());
    }

    protected function providerHas(Request $request)
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

    public function has(Request $request)
    {
        if ($this->localHas($request)) {
            return true;
        }

        return $this->providerHas($request);
    }
}
