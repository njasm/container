<?php

namespace Njasm\Container\Definition;

use Njasm\Container\Definition\AbstractDefinition;

class FactoryDefinition extends AbstractDefinition
{   
    protected function setType()
    {
        $this->type = Types::FACTORY;
    }
    
    protected function validateValue($value)
    {
        if (!$value instanceof \Closure) {
            throw new \InvalidArgumentException("Only Closures allowed.");
        }  
    }
}
