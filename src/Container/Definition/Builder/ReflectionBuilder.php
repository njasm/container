<?php

namespace Njasm\Container\Definition\Builder;

use Njasm\Container\Definition\Service\DependencyBag;
use Njasm\Container\Definition\Service\Request;
use Njasm\Container\Exception\ContainerException;
use Njasm\Container\Definition\Definition;
use Njasm\Container\Definition\DefinitionType;

class ReflectionBuilder implements BuilderInterface
{
    public function execute(Request $request)
    {
        $concrete   = $request->getConcrete();
        $reflected  = new \ReflectionClass($concrete);

        // abstract class or interface?
        if (!$reflected->isInstantiable()) {
            throw new ContainerException('Non-instantiable class [' . $reflected->name . ']');
        }

        $constructor = $reflected->getConstructor();

        if (is_null($constructor)) {
            $cacheDefinition = function() use ($reflected) {
                return $reflected->newInstanceArgs();
            };

            $bag = new DependencyBag(array(), $request->getDefaultProperties(), $request->getDefaultMethodCalls());
            $definition = new Definition($request->getKey(), $cacheDefinition, DefinitionType::CLOSURE_CACHE, $bag);
            $request->getDefinitionsMap()->add($definition);

            return $reflected->newInstanceArgs();
        }

        $parameters = $this->getConstructorArguments($constructor, $request);

        $cacheDefinition = function(array $suppliedParameters = []) use ($reflected, $parameters) {
            return $reflected->newInstanceArgs(empty($suppliedParameters) ? $parameters : $suppliedParameters);
        };

        $bag = new DependencyBag($parameters, $request->getDefaultProperties(), $request->getDefaultMethodCalls());
        $definition = new Definition($request->getKey(), $cacheDefinition, DefinitionType::CLOSURE_CACHE, $bag);
        $request->getDefinitionsMap()->add($definition);

        return $reflected->newInstanceArgs($parameters);
    }

    protected function getConstructorArguments(\ReflectionMethod $constructor, Request $request)
    {
        $defaultArguments = $request->getDefaultConstructorArguments();
        $arguments = $request->getConstructorArguments();

        if (!empty($arguments)) {
            $parameters = $arguments;
        } elseif (!empty($defaultArguments)) {
            $parameters = $defaultArguments;
        } else {
            $parameters = $this->getDependencies($constructor, $request->getContainer());
        }

        return $parameters;
    }

    protected function getDependencies(\ReflectionMethod $constructor, $container)
    {
        $parameters = array();
        foreach ($constructor->getParameters() as $param) {
            if (!$param->isDefaultValueAvailable()) {
                $parameters[] = $this->getDependency($param, $container);
                continue;
            }

            $parameters[] = $param->getDefaultValue();
        }

        return $parameters;
    }

    protected function getDependency(\ReflectionParameter $param, $container)
    {
        $dependency = $param->getClass();

        if (is_null($dependency)) {
            throw new ContainerException('Unable to resolve [' . $param->name .'] in ' . $param->getDeclaringClass()->getName());
        }

        return $container->get($dependency->name);
    }
}
