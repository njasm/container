<?php

namespace Njasm\Container\Definition\Service;

use Njasm\Container\Definition\Service\Request;

class LocalFinder extends FinderHandler
{
    protected function handle(Request $request)
    {
        $key = $request->getKey();
        $definitions = $request->getDefinitions();
        
        return array_key_exists($key, $definitions);
    }
}
