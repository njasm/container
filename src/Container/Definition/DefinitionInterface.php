<?php

namespace Njasm\Container\Definition;

interface DefinitionInterface
{
    public function getKey() : string;
    public function getConcrete();
    public function getType() : string;

    public function setConstructorArgument(int $argumentIndex, mixed $value);
    public function setConstructorArguments(array $arguments);
    public function getConstructorArguments();
    public function getConstructorArgument(int $index);

    public function setProperty(string $propertyName, mixed $value);
    public function setProperties(array $properties);
    public function getProperty(string $propertyName);
    public function getProperties() : array;

    public function callMethod(string $methodName, array $methodArguments = []) : void;
    public function callMethods(array $methods);
    public function getCallMethod($methodName);
    public function getCallMethods();
}
