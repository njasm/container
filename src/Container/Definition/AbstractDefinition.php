<?php

namespace Njasm\Container\Definition;

abstract class AbstractDefinition
{   
    protected $key;
    protected $concrete;
    protected $type;
    
    public function __construct($key, $concrete, $type)
    {
        $this->validateKey($key);
        $this->validateConcrete($concrete);
        $this->key = $key;
        $this->concrete = $concrete;
        $this->type = $type;
    }
    
    abstract protected function validateConcrete($concrete);

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
    
    public function getConcrete()
    {
        return $this->concrete;
    }
    
    public function getType()
    {
        return $this->type;
    }
}
