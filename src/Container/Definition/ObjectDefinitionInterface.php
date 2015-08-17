<?php

namespace Njasm\Container\Definition;

interface ObjectDefinitionInterface
{
    public function setProperty($propertyName, $value);
    public function setProperties(array $properties);
    public function getProperties();

    public function callMethod($methodName, array $methodArguments = array());
    public function callMethods(array $methods);
    public function getCallMethods();
}
