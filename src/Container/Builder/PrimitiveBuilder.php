<?php

namespace Njasm\Container\Builder;

use Njasm\Container\Definition\AbstractDefinition;

class PrimitiveBuilder implements BuilderInterface
{
    public function build(AbstractDefinition $definition)
    {
        return $definition->getDefinition();
    }
}
