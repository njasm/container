<?php

namespace Njasm\Container\Definition\Builder;

class ClosureBuilder implements BuilderInterface
{
    public function execute($definition)
    {
        return $definition();
    }
}
