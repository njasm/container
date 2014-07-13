<?php

namespace Njasm\Container\Definition\Builder;

class SingletonBuilder implements BuilderInterface
{
    public function execute($definition)
    {
        return $definition;
    }
}
