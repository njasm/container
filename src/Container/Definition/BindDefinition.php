<?php

namespace Njasm\Container\Definition;

use Njasm\Container\ServicesProviderInterface;
use Njasm\Container\Exception\ContainerException;

class BindDefinition extends AbstractDefinition implements ObjectDefinitionInterface
{
    /** @var \ReflectionClass */
    protected $concrete;

    /** @var array */
    protected $defaultConstructor = array();

    /** @var array */
    protected $defaultProperties = array();

    /** @var array */
    protected $defaultMethods = array();

    public function __construct(
        $key,
        \ReflectionClass $concrete,
        ServicesProviderInterface $container,
        array $constructor = array(),
        array $properties = array(),
        array $methods = array()
    ) {
        parent::__construct($key, $container);
        $this->concrete = $concrete;
        $this->defaultConstructor = $constructor;
        $this->defaultProperties = $properties;
        $this->defaultMethods = $methods;
    }

    public function build(array $constructor = array(), array $properties = array(), array $methods = array())
    {
        $reflectionMethod = $this->concrete->getConstructor();

        if (is_null($reflectionMethod)) {
            $parameters = array();
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
            call_user_func_array(array($object, $method), $value);
        }

        return $object;
    }

    protected function getConstructorArguments(\ReflectionMethod $constructor)
    {
        $parameters = array();
        foreach ($constructor->getParameters() as $param) {
            if (!$param->isDefaultValueAvailable()) {
                $dependency = $param->getClass();
                if (is_null($dependency)) {
                    throw new ContainerException('Unable to resolve parameter [' . $param->name .'] in ' . $param->getDeclaringClass()->getName());
                }

                $parameters[] = $this->container->get($dependency->name);
                continue;
            }

            $parameters[] = $param->getDefaultValue();
        }

        return $parameters;
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
