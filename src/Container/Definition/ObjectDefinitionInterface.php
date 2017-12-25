<?php

namespace Njasm\Container\Definition;

interface ObjectDefinitionInterface
{
    /**
     * the property and value to set on the object.
     *
     * @param string $propertyName the object's property name.
     * @param mixed $value the value to be set on the property.
     * @return ObjectDefinitionInterface
     */
    public function setProperty(string $propertyName, $value) : ObjectDefinitionInterface;

    /**
     * key value pair of propertyName => value to be set on the object.
     *
     * @param array $properties
     * @return ObjectDefinitionInterface
     */
    public function setProperties(array $properties) : ObjectDefinitionInterface;
    public function getProperties() : array;

    public function callMethod(string $methodName, array $methodArguments = []) : ObjectDefinitionInterface;
    public function callMethods(array $methods) : ObjectDefinitionInterface;
    public function getCallMethods() : array;
}
