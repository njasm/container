<?php

namespace Njasm\Container\Definition\Finder;

use Njasm\Container\Definition\Request;

class LocalFinder extends AbstractFinder
{
    protected function handle(Request $request)
    {
        $key = $request->getKey();
        $definitions = $request->getDefinitions();
        
        return array_key_exists($key, $definitions);
    }
}
