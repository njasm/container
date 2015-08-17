<?php

namespace Njasm\Container\Definition;

use Njasm\Container\ServicesProviderInterface;

class ProviderDefinition extends AbstractDefinition
{
    public function __construct($key, ServicesProviderInterface $container)
    {
        parent::__construct($key, $container);
    }

    public function build(array $constructor = array(), array $properties = array(), array $methods = array())
    {
        $providers = $this->container->getProviders();
        foreach($providers as $provider) {
            if ($provider->has($this->key)) {
                return $provider->get($this->key);
            }
        }
    }
}
