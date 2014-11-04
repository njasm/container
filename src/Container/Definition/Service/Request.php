<?php

namespace Njasm\Container\Definition\Service;

use Njasm\Container\Definition\DefinitionsMap;
use Njasm\Container\ServicesProviderInterface;

class Request
{
    protected $key;
    protected $container;
    protected $definitionsMap;
    protected $providers;
    protected $paramsToInject;
    protected $methodsToCall;
    
    public function __construct(
        $key,
        DefinitionsMap $definitionsMap,
        array $providers,
        ServicesProviderInterface $container,
        $params = array(),
        $methods = array()
    ) {
        $key = trim($key);
        
        if (empty($key)) {
            throw new \InvalidArgumentException("Key cannot be empty.");
        }
        
        $this->key              = $key;
        $this->definitionsMap   = $definitionsMap;
        $this->providers        = $providers;
        $this->container        = $container;
        $this->paramsToInject   = $params;
        $this->methodsToCall    = $methods;
    }

    /**
     * Returns this Request Key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns the Container.
     *
     * @return ServicesProviderInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns the Definitions Map.
     *
     * @return DefinitionsMap
     */
    public function getDefinitionsMap()
    {
        return $this->definitionsMap;
    }

    /**
     * Returns all Sub-Containers registered.
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Returns this Key's concrete value.
     *
     * @return mixed
     */
    public function getConcrete()
    {
        return $this->getDefinition()->getConcrete();
    }

    protected function definitionExists()
    {
        return $this->definitionsMap->has($this->key);
    }

    protected function getDefinition()
    {
        return $this->definitionsMap->get($this->key);
    }

    /**
     * Properties to be injected, when service was requested to the container.
     *
     * @return array
     */
    public function getParamsToInject()
    {
        return $this->paramsToInject;
    }

    /**
     * Methods to call, when service was requested to the container.
     *
     * @return array
     */
    public function getMethodCalls()
    {
        return $this->methodsToCall;
    }

    /**
     * Default Parameters and values to set when this service was registered.
     *
     * @return array
     */
    public function getDefaultParamsToInject()
    {
        if (!$this->definitionExists()) {
            return array();
        }

        return $this->getDefinition()->getParamsToInject();
    }

    /**
     * Default Methods to call when this service was registered.
     *
     * @return array
     */
    public function getDefaultMethodCalls()
    {
        if (!$this->definitionExists()) {
            return array();
        }

        return $this->getDefinition()->getMethodsToCall();
    }
}
