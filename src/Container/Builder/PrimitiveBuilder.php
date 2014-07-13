<?php

namespace Njasm\Container\Builder;

use Njasm\Container\Definition\AbstractDefinition;

class PrimitiveBuilder implements BuilderInterface
{
    public function execute(AbstractDefinition $definition)
    {
        return $definition->getDefinition();
    }
}
