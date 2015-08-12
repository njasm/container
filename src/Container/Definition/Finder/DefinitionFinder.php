<?php

namespace Njasm\Container\Definition\Finder;

use Njasm\Container\Definition\Service\Request;

class DefinitionFinder
{
    protected $isLocal = false;
    protected $isProvider = false;

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
            $this->isProvider = false;

            return $this->isLocal = true;
        }
        if ($this->providerHas($request)) {
            $this->isLocal = false;

            return $this->isProvider = true;
        }

        return false;
    }

    public function foundInLocal()
    {
        return $this->isLocal;
    }

    public function foundInProvider()
    {
        return $this->isProvider;
    }

}
