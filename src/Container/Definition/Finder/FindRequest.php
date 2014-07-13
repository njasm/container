<?php

namespace Njasm\Container\Definition\Finder;

class FindRequest
{
    protected $key;
    protected $definitions;
    protected $providers;
    
    public function __construct($key, $definitions, $providers)
    {
        $this->key = $key;
        $this->definitions = $definitions;
        $this->providers = $providers;
    }
    
    public function getKey()
    {
        return $this->key;
    }
    
    public function getDefinitions()
    {
        return $this->definitions;
    }
    
    public function getProviders()
    {
        return $this->providers;
    }
}
