<?php

namespace Njasm\Container\Definition;

class Definition implements DefinitionInterface
{
    protected $key;
    protected $concrete;
    protected $type;

    protected $paramsToInject;
    protected $methodsToCall;
    
    public function __construct(
        $key, $concrete, DefinitionType $type, array $params = array(), array $methods = array()
    ) {
        if (empty($key)) {
            throw new \InvalidArgumentException("key cannot be empty.");
        }

        $this->key = $key;
        $this->concrete = $concrete;
        $this->type = $type;
        $this->paramsToInject = $params;
        $this->methodsToCall = $methods;
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
        return $this->type->__toString();
    }

    public function getParamsToInject()
    {
        return $this->paramsToInject;
    }

    public function getMethodsToCall()
    {
        return $this->methodsToCall;
    }
}

