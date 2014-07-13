<?php

namespace Njasm\Container\Definition;

use Njasm\Container\Definition\AbstractDefinition;

class PrimitiveDefinition extends AbstractDefinition
{   
    protected function setType()
    {
        $this->type = DefinitionType::PRIMITIVE;
    }
    
    protected function validateValue($value)
    {
        if (is_object($value)) {
            throw new \InvalidArgumentException("Only primitive data types allowed.");
        }  
    }
}
