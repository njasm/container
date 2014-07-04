<?php

namespace Njasm\Container\Definition\Service;

use Njasm\Container\Definition\Service\Request;

class LocalFinder extends AbstractFinder
{
    protected function process(Request $request)
    {
        $key = $request->getKey();
        $definitions = $request->getDefinitions();
        
        return array_key_exists($key, $definitions);
    }
}
