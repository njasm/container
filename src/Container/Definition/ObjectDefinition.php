<?php

namespace Njasm\Container\Definition;

class ObjectDefinition extends AbstractDefinition
{ 
    public function validateConcrete($concrete)
    {
        if (is_object($concrete) && !$concrete instanceof \Closure) {
            return true;
        }
        
        throw new \InvalidArgumentException("Only object types are allowed.");
    }
}
