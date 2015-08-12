<?php

namespace Njasm\Container\Factory;

use Njasm\Container\Definition\Builder\ReflectionBuilder;
use Njasm\Container\Definition\DefinitionType;
use Njasm\Container\Definition\Service\Request;

class DefinitionFactory implements FactoryInterface
{
    protected $buildersNamespace;
    protected $buildersSuffix;

    const BUILDERS_INTERFACE = 'Njasm\Container\Definition\Builder\BuilderInterface';

    public function __construct(
        $buildersNamespace = 'Njasm\Container\Definition\Builder\\',
        $buildersSuffix = 'Builder'
    ) {
        $this->buildersNamespace = $buildersNamespace;
        $this->buildersSuffix = $buildersSuffix;
    }

    public function build(Request $request)
    {
        $defType = null;
        if ($request->getDefinitionsMap()->has($request->getKey())) {
            $defType = $request->getDefinitionsMap()->get($request->getKey())->getType();
        }

        switch ($defType) {
            case DefinitionType::ALIAS :
                return $this->buildAlias($request);
            case DefinitionType::CLOSURE :
                return $this->buildClosure($request);
            case DefinitionType::OBJECT :
                return $this->buildObject($request);
            case DefinitionType::PRIMITIVE :
                return $this->buildPrimitive($request);
            case DefinitionType::REFLECTION :
                return $this->buildReflection($request);
            default :
                return $this->buildFromProviders($request);

                //throw new \OutOfBoundsException("No Available builder to create the requested service.");
        }
    }

    protected function buildAlias(Request $request)
    {
        $value = $request->getConcrete();

        return $request->getContainer()->get($value);
    }

    protected function buildClosure(Request $request)
    {
        $concrete = $request->getConcrete();
        $arguments = $request->getConstructorArguments();
        $closure = new \ReflectionFunction($concrete);

        return $closure->invokeArgs($arguments);
    }

    protected function buildObject(Request $request)
    {
        return $request->getConcrete();
    }

    protected function buildPrimitive(Request $request)
    {
        return $request->getConcrete();
    }

    protected function buildReflection(Request $request)
    {
        $builder = new ReflectionBuilder();

        return $builder->execute($request);
    }

    protected function buildFromProviders(Request $request)
    {
        $key = $request->getKey();

        foreach ($request->getProviders() as $provider) {
            if ($provider->has($key)) {
                return $provider->get($key);
            }
        }
    }
}
