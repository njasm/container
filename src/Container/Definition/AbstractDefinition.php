<?php

namespace Njasm\Container\Definition;

use Psr\Container\ContainerInterface;

abstract class AbstractDefinition
{
    /** @var string */
    protected $key;

    /** @var ContainerInterface */
    protected $container;

    public function __construct(string $key, ContainerInterface $container)
    {
        $this->container = $container;
        $this->key = $key;
    }

    /**
     * Builds the value to be returned.
     *
     * @param array $constructor the ctor dependencies to be injected (overriding any previous defined ctor dependencies)
     * @param array $properties any property values to be set if the properties exists in the object being built.
     * @param array $methods methods to be called with arguments before returning it.
     *
     * @return mixed
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    abstract public function build(array $constructor = [], array $properties = [], array $methods = []);
}
