<?php

namespace Njasm\Container\Definition\Builder;

use Njasm\Container\Definition\Service\Request;

class ClosureBuilder implements BuilderInterface
{
    public function execute(Request $request)
    {
        $concrete = $request->getConcrete();

        return $concrete();
    }
}
