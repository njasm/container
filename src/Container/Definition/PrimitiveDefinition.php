<?php

namespace Njasm\Container\Definition;

use Njasm\Container\Definition\AbstractDefinition;

class PrimitiveDefinition extends AbstractDefinition
{   
    protected function validateConcrete($concrete)
    {
        if (is_object($concrete)) {
            throw new \InvalidArgumentException("Only primitive data types allowed.");
        }  
    }
}
