<?php

namespace Njasm\Container\Definition;

use Njasm\Container\ServicesProviderInterface;

class ObjectDefinition extends AbstractDefinition implements ObjectDefinitionInterface
{
    /** @var Object */
    protected $concrete;

    /** @var array */
    protected $defaultProperties = array();

    /** @var array */
    protected $defaultMethods = array();

    public function __construct(
        $key,
        $concrete,
        ServicesProviderInterface $container,
        array $properties = array(),
        array $methods = array()
    ) {
        parent::__construct($key, $container);
        $this->concrete = $concrete;
        $this->defaultProperties = $properties;
        $this->defaultMethods = $methods;
    }

    public function build(array $constructor = array(), array $properties = array(), array $methods = array())
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


    public function getProperties()
    {
        return $this->defaultProperties;
    }

    public function setProperties(array $properties)
    {
        $this->defaultProperties = $properties;

        return $this;
    }

    public function setProperty($propertyName, $value)
    {
        $this->defaultProperties[$propertyName] = $value;

        return $this;
    }

    public function callMethod($methodName, array $methodArguments = array())
    {
        $this->defaultMethods[$methodName] = $methodArguments;

        return $this;
    }

    public function callMethods(array $methods)
    {
        $this->defaultMethods = $methods;

        return $this;
    }

    public function getCallMethods()
    {
        return $this->defaultMethods;
    }
}
