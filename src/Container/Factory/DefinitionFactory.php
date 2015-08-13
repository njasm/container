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

                return $request->getContainer()->get($request->getConcrete());

            case DefinitionType::CLOSURE_CACHE :

                $concrete   = $request->getConcrete();
                $arguments  = $request->getConstructorArguments();

                return call_user_func_array($concrete, array($arguments));

            case DefinitionType::CLOSURE :

                $concrete   = $request->getConcrete();
                $arguments  = $request->getConstructorArguments();

                return call_user_func_array($concrete, $arguments);

            case DefinitionType::OBJECT :
            case DefinitionType::PRIMITIVE :

                return $request->getConcrete();

            case DefinitionType::REFLECTION :

                $builder = new ReflectionBuilder();

                return $builder->execute($request);

            default :
                $key = $request->getKey();

                foreach ($request->getProviders() as $provider) {
                    if ($provider->has($key)) {
                        return $provider->get($key);
                    }
                }
        }
    }
}
