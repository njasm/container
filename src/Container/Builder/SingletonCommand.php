<?php

namespace Njasm\Container\Builder;

use Njasm\Container\Definition\AbstractDefinition;

class SingletonCommand implements BuilderCommand
{
    public function execute(AbstractDefinition $definition)
    {
        return $definition->getDefinition();
    }
}
