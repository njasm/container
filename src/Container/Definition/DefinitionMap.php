<?php

namespace Njasm\Container\Definition;

use Njasm\Container\Definition\AbstractDefinition;

class DefinitionMap extends \ArrayObject
{
    public function add(AbstractDefinition $definition)
    {
        $this[$definition->getKey()] = $definition;
    }
    
    public function has($key)
    {
        return isset($this[$key]);
    }
}
