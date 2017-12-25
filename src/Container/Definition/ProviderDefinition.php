<?php

namespace Njasm\Container\Definition;

use Psr\Container\ContainerInterface;

class ProviderDefinition extends AbstractDefinition
{
    public function __construct($key, ContainerInterface $container)
    {
        parent::__construct($key, $container);
    }

    public function build(array $constructor = [], array $properties = [], array $methods = [])
    {
        $providers = $this->container->getProviders();
        foreach($providers as $provider) {
            if ($provider->has($this->key)) {
                return $provider->get($this->key);
            }
        }
    }
}
