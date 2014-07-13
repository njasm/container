<?php

namespace Njasm\Container\Builder;

use Njasm\Container\Definition\AbstractDefinition;

class SingletonBuilder implements BuilderInterface
{
    public function execute(AbstractDefinition $definition)
    {
        return $definition->getDefinition();
    }
}
