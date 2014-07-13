<?php

namespace Njasm\Container\Definition\Finder;

class LocalFinder extends AbstractFinder
{
    protected function handle(FindRequest $request)
    {
        $key = $request->getKey();
        $definitions = $request->getDefinitions();
        
        return array_key_exists($key, $definitions);
    }
}
