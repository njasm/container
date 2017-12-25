<?php

namespace Njasm\Container;

use Njasm\Container\Definition\AbstractDefinition;
use Njasm\Container\Definition\AliasDefinition;
use Njasm\Container\Definition\BindDefinition;
use Njasm\Container\Definition\ClosureDefinition;
use Njasm\Container\Definition\ObjectDefinition;
use Njasm\Container\Definition\ProviderDefinition;
use Njasm\Container\Definition\ValueDefinition;
use Njasm\Container\Exception\CircularDependencyException;
use Njasm\Container\Exception\NotFoundException;
use Njasm\Container\Exception\ContainerException;

use Psr\Container\ContainerInterface;

class Container implements ServiceProviderInterface
{
    /**
     * @var array Definitions map.
     */
    protected $definitionsMap;

    /**
     * @var array Nested service providers.
     */
    protected $providers;

    /**
     * @var array Singleton services instances.
     */
    protected $registry;

    /**
     * @var array Singleton services map.
     */
    protected $singletons;

    /**
     * @var array Service keys being built.
     */
    protected $buildingKeys = array();

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Initializes the Container.
     *
     * @return void
     */
    protected function initialize()
    {
        $this->providers = [];
        $this->registry = [];
        $this->singletons = [];
        $this->definitionsMap = [];
        
        $this->set(self::class, $this);
        $this->alias('Container', self::class);
    }

    /**
     * @inheritdoc
     */
    public function has($id)
    {
        if (isset($this->definitionsMap[$id])) {
            return true;
        }

        return $this->providersHas($id);
    }

    /**
     * Check if service is registered in nested Containers.
     *
     * @param   string      $key
     * @return  boolean
     */
    protected function providersHas($key)
    {
        foreach ($this->providers as $provider) {
            if ($provider->has($key)) {
                $this->definitionsMap[$key] = new ProviderDefinition($key, $this);

                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function set(
        string $key, $concrete, array $construct = [], array $properties = [], array $methods = []
    ) : AbstractDefinition {

        if ($concrete instanceof \Closure) {
            $definition = new ClosureDefinition($key, $concrete, $this, $construct);
        } elseif (is_object($concrete)) {
            $definition = new ObjectDefinition($key, $concrete, $this, $properties, $methods);
        } else {
            $definition = new ValueDefinition($key, $concrete, $this);
            $this->singletons[$key] = true;
        }

        $this->definitionsMap[$key] = $definition;

        return $definition;
    }

    /**
     * @inheritdoc
     */
    public function alias(string $alias, string $key) : AbstractDefinition
    {
        $definition = new AliasDefinition($alias, $key, $this);
        $this->definitionsMap[$alias] = $definition;

        return $definition;
    }

    /**
     * Bind a key to a FQCN accessible by autoload and instantiable.
     *
     * @param   string      $key
     * @param   string      $concrete  FQCN
     * @param   array       $construct
     * @param   array       $properties
     * @param   array       $methods
     * @return  Definition\BindDefinition
     */
    public function bind(
        $key,
        $concrete,
        array $construct = array(),
        array $properties = array(),
        array $methods = array()
    ) {
        try {
            $reflected = new \ReflectionClass($concrete);
            if (!$reflected->isInstantiable()) {
                throw new ContainerException($reflected->getName() . ' Is Not Instantiable.');
            }
            $concrete = $reflected;
        } catch (\Throwable $e) {
            throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
        }

        $definition = new BindDefinition($key, $concrete, $this, $construct, $properties, $methods);
        $this->definitionsMap[$key] = $definition;

        return $definition;
    }

    /**
     * Bind a key to a FQCN accessible by autoload and instantiable, registering it as a Singleton.
     *
     * @param   string      $key
     * @param   string      $concrete   FQCN
     * @param   array       $construct
     * @param   array       $properties
     * @param   array       $methods
     * @return  Definition\Definition
     */
    public function bindSingleton(
        $key,
        $concrete,
        array $construct = array(),
        array $properties = array(),
        array $methods = array()
    ) {
        $definition = $this->bind($key, $concrete, $construct, $properties, $methods);
        $this->singletons[$key] = true;

        return $definition;
    }

    /**
     * Registers service as a singleton instance in the container.
     *
     * @param   string      $key
     * @param   mixed       $concrete
     * @param   array       $construct
     * @param   array       $properties
     * @param   array       $methods
     * @return  Definition\Definition
     */
    public function singleton(
        $key,
        $concrete,
        array $construct = array(),
        array $properties = array(),
        array $methods = array()
    ) {
        $definition = $this->set($key, $concrete, $construct, $properties, $methods);
        $this->singletons[$key] = true;

        return $definition;
    }

    /**
     * Registers another services provider container.
     *
     * @param   ContainerInterface   $provider
     * @return  Container
     */
    public function provider(ContainerInterface $provider)
    {
        $this->providers[] = $provider;

        return $this;
    }

    /**
     * Returns the service.
     *
     * @param   string      $key
     * @param   array       $construct
     * @param   array       $properties
     * @param   array       $methods
     * @return  mixed
     *
     * @throws  NotFoundException
     */
    public function get($key, array $construct = array(), array $properties = array(), array $methods = array())
    {
        if (isset($this->registry[$key])) {
            return $this->registry[$key];
        }

        // circular dependency guard
        if (array_key_exists($key, $this->buildingKeys)) {
            throw new CircularDependencyException("Circular Dependency detected for {$key}");
        }

        $this->buildingKeys[$key] = true;

        if (!$this->has($key)) {
            // try to bail-out client called service. We'll assemble a new reflection definition and will,
            // if class exists, try to resolve all dependencies and instantiate the object if possible.
            $this->definitionsMap[$key] = $this->bind($key, $key, $construct, $properties, $methods);
        }

        $returnValue = $this->definitionsMap[$key]->build($construct, $properties, $methods);
        unset($this->buildingKeys[$key]);

        if (isset($this->singletons[$key])) {
            return $this->registry[$key] = $returnValue;
        }

        return $returnValue;
    }

    /**
     * @inheritdoc
     */
    public function remove($key) : bool
    {
        if (isset($this->definitionsMap[$key])) {
            unset($this->definitionsMap[$key]);
            unset($this->registry[$key]);
            unset($this->singletons[$key]);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function reset() : void
    {
        $this->initialize();
    }

    /**
     * Check if service is registered as a singleton.
     *
     * @param   string  $key
     * @return  boolean
     */
    protected function isSingleton($key)
    {
        return isset($this->singletons[$key]);
    }

    /**
     * @inheritdoc
     */
    public function getProviders() : array
    {
        return $this->providers;
    }
}
