<?php

namespace Njasm\Container\Builder;

use Njasm\Container\Definition\AbstractDefinition;

class SingletonBuilder implements BuilderInterface
{
    public function build(AbstractDefinition $definition)
    {
        return $definition->getDefinition();
    }
}
