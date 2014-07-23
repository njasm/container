<?php

namespace Njasm\Container\Definition;

use Njasm\Container\Definition\AbstractDefinition;

class ReflectionDefinition extends AbstractDefinition
{   
    protected function validateConcrete($concrete)
    {
        if (empty($concrete)) {
            throw new \InvalidArgumentException("Empty string not allowed.");
        }  
    }
}
