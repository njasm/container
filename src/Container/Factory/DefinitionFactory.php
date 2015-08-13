<?php

namespace Njasm\Container\Factory;

use Njasm\Container\Definition\Builder\ReflectionBuilder;
use Njasm\Container\Definition\DefinitionType;
use Njasm\Container\Definition\Service\Request;

class DefinitionFactory implements FactoryInterface
{
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
            case DefinitionType::PRIMITIVE :
                return $this->buildConcrete($request);
            case DefinitionType::REFLECTION :
                return $this->buildReflection($request);
            default :
                return $this->buildFromProviders($request);
        }
    }

    protected function buildAlias(Request $request)
    {
        $value = $request->getConcrete();

        return $request->getContainer()->get($value);
    }

    protected function buildClosure(Request $request)
    {
        $concrete           = $request->getConcrete();
        $arguments          = $request->getConstructorArguments();
        $closure            = new \ReflectionFunction($concrete);
        $params             = $closure->getParameters();

        if (count($params) > 0 && $params[0]->isArray()) {
            $arguments = array($arguments);
        }

        return $closure->invokeArgs($arguments);
    }

    protected function buildConcrete(Request $request)
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
