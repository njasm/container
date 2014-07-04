<?php

namespace Njasm\Container\Definition;

use Njasm\Container\Definition\Types;

abstract class AbstractDefinition
{   
    protected $key;
    protected $value;
    protected $type;
    
    public function __construct($key, $value)
    {
        $this->validateKey($key);
        $this->validateValue($value);
        $this->key = $key;
        $this->value = $value;
        $this->setType();
    }
    
    abstract protected function validateValue($value);
    abstract protected function setType();

    protected function validateKey($key)
    {
        if(empty($key) || !is_string($key)) {
            throw new \InvalidArgumentException();
        }
    }
    
    public function getKey()
    {
        return $this->key;
    }
    
    public function getDefinition()
    {
        return $this->value;
    }
    
    public function getType()
    {
        return $this->type;
    }
}
