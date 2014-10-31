<?php

namespace Njasm\Container\Definition\Service;

use Njasm\Container\Definition\DefinitionMap;
use Njasm\Container\ServicesProviderInterface;

class Request
{
    protected $key;
    protected $container;
    protected $definitionsMap;
    protected $providers;

    /**
     * @param $key string
     * @param DefinitionMap $definitionMap
     * @param array $providers
     * @param ServicesProviderInterface $container
     */
    public function __construct(
        $key,
        DefinitionMap $definitionMap,
        array $providers,
        ServicesProviderInterface $container
    ) {
        $key = trim($key);
        
        if (empty($key)) {
            throw new \InvalidArgumentException("Key cannot be empty.");
        }
        
        $this->key              = $key;
        $this->definitionMap    = $definitionMap;
        $this->providers        = $providers;
        $this->container        = $container;
    }
    
    public function getKey()
    {
        return $this->key;
    }
    
    public function getContainer()
    {
        return $this->container;
    }
    
    public function getDefinitionsMap()
    {
        return $this->definitionMap;
    }
    
    public function getProviders()
    {
        return $this->providers;
    }
}
