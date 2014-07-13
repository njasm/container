<?php

namespace Njasm\Container\Definition;

use Njasm\Container\Definition\AbstractDefinition;

class FactoryDefinition extends AbstractDefinition
{   
    protected function setType()
    {
        $this->type = DefinitionType::FACTORY;
    }
    
    protected function validateValue($value)
    {
        if (!$value instanceof \Closure) {
            throw new \InvalidArgumentException("Only Closures allowed.");
        }  
    }
}
