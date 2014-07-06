<?php

namespace Njasm\Container\Builder;

use Njasm\Container\Definition\AbstractDefinition;

class FactoryCommand implements BuilderCommand
{
    public function execute(AbstractDefinition $definition)
    {
        $value = $definition->getDefinition();
        return $value();
    }
}
