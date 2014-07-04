<?php

namespace Njasm\Container\Builder;

use Njasm\Container\Definition\AbstractDefinition;

class FactoryBuilder implements BuilderInterface
{
    public function build(AbstractDefinition $definition)
    {
        $value = $definition->getDefinition();
        return $value();
    }
}
