<?php

namespace Njasm\Container\Definition\Builder;

class FactoryBuilder implements BuilderInterface
{
    public function execute($definition)
    {
        return $definition();
    }
}
