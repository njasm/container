<?php

namespace Njasm\Container\Definition;

use Njasm\Container\Exception\UnresolvableDependencyException;
use Psr\Container\ContainerInterface;

class BindDefinition extends AbstractDefinition implements ObjectDefinitionInterface
{
    /** @var \ReflectionClass */
    protected $concrete;

    /** @var array */
    protected $defaultConstructor = [];

    /** @var string[mixed] */
    protected $defaultProperties = [];

    /** @var array */
    protected $defaultMethods = [];

    public function __construct(
        string $key, \ReflectionClass $concrete, ContainerInterface $container,
        array $constructor = [], array $properties = [], array $methods = []
    ) {
        parent::__construct($key, $container);
        $this->concrete = $concrete;
        $this->defaultConstructor = $constructor;
        $this->defaultProperties = $properties;
        $this->defaultMethods = $methods;
    }

    public function build(array $constructor = [], array $properties = [], array $methods = [])
    {
        $reflectionMethod = $this->concrete->getConstructor();

        if (is_null($reflectionMethod)) {
            $parameters = [];
        } elseif (!empty($constructor)) {
            $parameters = $constructor;
        } elseif (!empty($this->defaultConstructor)) {
            $parameters = $this->defaultConstructor;
        } else {
            $parameters = $this->getConstructorArguments($reflectionMethod);
        }

        $object = $this->concrete->newInstanceArgs($parameters);

        $properties = !empty($properties) ? $properties : $this->defaultProperties;
        foreach($properties as $property => $value) {
            $object->{$property} = $value;
        }

        $methods = !empty($methods) ? $methods : $this->defaultMethods;
        foreach($methods as $method => $value) {
            call_user_func_array([$object, $method], $value);
        }

        return $object;
    }

    protected function getConstructorArguments(\ReflectionMethod $constructor) : array
    {
        $parameters = [];
        foreach ($constructor->getParameters() as $param) {
            if (!$param->isDefaultValueAvailable()) {
                $dependency = $param->getClass();
                if (is_null($dependency)) {
                    throw new UnresolvableDependencyException(
                        'Unable to resolve parameter [' . $param->name .'] in '
                            . $param->getDeclaringClass()->getName()
                    );
                }

                $parameters[] = $this->container->get($dependency->name);
                continue;
            }

            $parameters[] = $param->getDefaultValue();
        }

        return $parameters;
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
