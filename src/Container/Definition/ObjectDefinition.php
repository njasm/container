<?php

namespace Njasm\Container\Definition;

class ObjectDefinition extends AbstractDefinition
{ 
    protected function setType()
    {
        $this->type = DefinitionType::OBJECT;
    }
    
    public function validateValue($value)
    {
        if (is_object($value) && !$value instanceof \Closure) {
            return true;
        }
        
        throw new \InvalidArgumentException("Only object types are allowed.");
    }
}
