<?php

namespace Njasm\Container\Definition\Builder;

class PrimitiveBuilder implements BuilderInterface
{
    public function execute($definition)
    {
        return $definition;
    }
}
