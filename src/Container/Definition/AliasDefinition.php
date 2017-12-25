<?php

namespace Njasm\Container\Definition;

use Psr\Container\ContainerInterface;

class AliasDefinition extends AbstractDefinition
{
    /** @var string */
    protected $concrete;

    public function __construct(string $key, string $concrete, ContainerInterface $container)
    {
        parent::__construct($key, $container);
        $this->concrete = $concrete;
    }

    /**
     * @inheritdoc
     */
    public function build(array $constructor = [], array $properties = [], array $methods = [])
    {
        return $this->container->get($this->concrete);
    }
}
