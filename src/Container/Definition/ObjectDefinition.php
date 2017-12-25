<?php

namespace Njasm\Container\Definition;

use Njasm\Container\ServiceProviderInterface;

class ObjectDefinition extends AbstractDefinition implements ObjectDefinitionInterface
{
    /** @var Object */
    protected $concrete;

    /** @var array */
    protected $defaultProperties = [];

    /** @var array */
    protected $defaultMethods = [];

    public function __construct(
        string $key, $concrete, ServiceProviderInterface $container,
        array $properties = [], array $methods = []
    ) {
        parent::__construct($key, $container);
        $this->concrete = $concrete;
        $this->defaultProperties = $properties;
        $this->defaultMethods = $methods;
    }

    public function build(array $constructor = [], array $properties = [], array $methods = [])
    {
        $properties = !empty($properties) ? $properties : $this->defaultProperties;
        foreach($properties as $property => $value) {
            $this->concrete->{$property} = $value;
        }

        $methods = !empty($methods) ? $methods : $this->defaultMethods;
        foreach($methods as $method => $value) {
           call_user_func_array(array($this->concrete, $method), $value);
        }

        return $this->concrete;
    }


    public function getProperties() : array
    {
        return $this->defaultProperties;
    }

    public function setProperties(array $properties) : ObjectDefinitionInterface
    {
        $this->defaultProperties = $properties;
        return $this;
    }

    public function setProperty(string $propertyName, $value) : ObjectDefinitionInterface
    {
        $this->defaultProperties[$propertyName] = $value;
        return $this;
    }

    public function callMethod(string $methodName, array $methodArguments = []) : ObjectDefinitionInterface
    {
        $this->defaultMethods[$methodName] = $methodArguments;
        return $this;
    }

    public function callMethods(array $methods) : ObjectDefinitionInterface
    {
        $this->defaultMethods = $methods;
        return $this;
    }

    public function getCallMethods() : array
    {
        return $this->defaultMethods;
    }
}
