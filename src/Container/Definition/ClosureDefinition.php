<?php

namespace Njasm\Container\Definition;

use Njasm\Container\Definition\AbstractDefinition;

class ClosureDefinition extends AbstractDefinition
{   
    protected function setType()
    {
        $this->type = DefinitionType::CLOSURE;
    }
    
    protected function validateValue($value)
    {
        if (!$value instanceof \Closure) {
            throw new \InvalidArgumentException("Only Closures allowed.");
        }  
    }
}
