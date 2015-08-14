<?php

namespace Njasm\Container;

use Njasm\Container\Definition\Definition;
use Njasm\Container\Definition\DefinitionType;
use Njasm\Container\Definition\DefinitionsMap;
use Njasm\Container\Definition\Service\DependencyBag;
use Njasm\Container\Definition\Service\Request;
use Njasm\Container\Exception\NotFoundException;
use Njasm\Container\Factory\DefinitionFactory;
use Njasm\Container\Exception\ContainerException;

class Container implements ServicesProviderInterface
{
    /**
     * @var \Njasm\Container\Definition\DefinitionsMap
     */
    protected $definitionsMap;

    /**
     * @var DefinitionFactory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $providers;

    /**
     * @var array
     */
    protected $registry;

    /**
     * @var array
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
        $this->providers = array();
        $this->registry = array();
        $this->singletons = array();
        $this->definitionsMap = new DefinitionsMap();
        $this->factory = new DefinitionFactory();
        
        $this->set('Njasm\Container\Container', $this);
        $this->alias('Container', 'Njasm\Container\Container');
    }

    /**
     * Check if service is registered.
     *
     * @param   string      $key
     * @return  boolean
     */
    public function has($key)
    {
        if ($this->definitionsMap->has($key)) {
            return true;
        }

        foreach ($this->providers as $provider) {
            if ($provider->has($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Register a new service in the container.
     *
     * @param   string      $key
     * @param   mixed       $concrete
     * @param   array       $construct
     * @param   array       $properties
     * @param   array       $methods
     * @return  Definition
     */
    public function set(
        $key,
        $concrete,
        array $construct = array(),
        array $properties = array(),
        array $methods = array()
    ) {
        $dependencyBag = new DependencyBag($construct, $properties, $methods);
        $definition = null;

        if ($concrete instanceof \Closure) {
            $definition = new Definition($key, $concrete, DefinitionType::CLOSURE, $dependencyBag);
        } elseif (is_object($concrete)) {
            $definition = new Definition($key, $concrete, DefinitionType::OBJECT, $dependencyBag);
            $this->singletons[$key] = true;
        } elseif (is_scalar($concrete)) {
            $definition = new Definition($key, $concrete, DefinitionType::PRIMITIVE, $dependencyBag);
            $this->singletons[$key] = true;
        }

        if (is_null($definition)) {
            throw new \OutOfBoundsException("Unknown definition type.");
        }

        $this->definitionsMap->add($definition);

        return $definition;
    }

    /**
     * Register an alias to a service key.
     *
     * @param   string      $alias
     * @param   string      $key
     * @return  Definition\Definition
     */
    public function alias($alias, $key)
    {
        $definition = new Definition($alias, $key, DefinitionType::ALIAS);
        $this->definitionsMap->add($definition);

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
     * @return  Definition\Definition
     */
    public function bind(
        $key,
        $concrete,
        array $construct = array(),
        array $properties = array(),
        array $methods = array()
    ) {
        $dependencyBag = new DependencyBag($construct, $properties, $methods);
        $definition = new Definition($key, $concrete, DefinitionType::REFLECTION, $dependencyBag);
        $this->definitionsMap->add($definition);

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
     * @param   ServicesProviderInterface   $provider
     * @return  Container
     */
    public function provider(ServicesProviderInterface $provider)
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

        $dependencyBag = new DependencyBag($construct, $properties, $methods);
        $request = new Request($key, $this, $dependencyBag);
        $returnValue = $this->build($request);

        if (is_object($returnValue) && !$returnValue instanceof \Closure) {
            $this->injectParams($returnValue, $request);
            $this->callMethods($returnValue, $request);
        }

        if (isset($this->singletons[$key])) {
            return $this->registry[$key] = $returnValue;
        }

        return $returnValue;
    }

    /**
     * Removes a service from the container.
     * This will NOT remove services from other nested providers.
     *
     * @param   string  $key
     * @return  boolean
     */
    public function remove($key)
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
     * Reset container settings.
     *
     * @return  void
     */
    public function reset()
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
     * Inject Properties of the Service.
     *
     * @param   mixed       $service
     * @param   Request     $request
     * @return  void
     */
    protected function injectParams($service, Request $request)
    {
        $paramsToInject = $request->getProperties();
        $defaultParams  = $request->getDefaultProperties();

        if (empty($paramsToInject)) {
            $paramsToInject = $defaultParams;
        }

        // array_map() might be faster or not, need further testing.
        foreach ($paramsToInject as $param => $value) {
            $service->{$param} = $value;
        }
    }

    /**
     * Call Methods of the Service.
     *
     * @param   mixed       $service
     * @param   Request     $request
     * @return  void
     */
    protected function callMethods($service, Request $request)
    {
        $methodsToCall = $request->getMethodCalls();
        $defaultMethods = $request->getDefaultMethodCalls();

        if (empty($methodsToCall)) {
            $methodsToCall = $defaultMethods;
        }

        // array_map() might be faster or not, need further testing.
        foreach ($methodsToCall as $methodName => $values) {
            call_user_func_array(array($service, $methodName), (array) $values);
        }
    }

    /**
     * Build the requested service.
     *
     * @param   Request     $request
     * @return  mixed
     */
    protected function build(Request $request)
    {
        $key = $request->getKey();

        // circular dependency guard
        if (array_key_exists($key, $this->buildingKeys)) {
            throw new ContainerException("Circular Dependency detected for {$key}");
        }

        $this->buildingKeys[$key] = true;

        if (!$this->has($key)) {
            // try to bail-out client called service. We'll assemble a new reflection definition and will,
            // if class exists, try to resolve all dependencies and instantiate the object if possible.
            $definition = new Definition($key, $key, DefinitionType::REFLECTION);
            $request->getDefinitionsMap()->add($definition);
        }

        $returnValue = $this->factory->build($request);
        unset($this->buildingKeys[$key]);

        return $returnValue;
    }

    /**
     * Returns Definitions Map.
     *
     * @return \Njasm\Container\Definition\DefinitionMap
     */
    public function getDefinitionsMap()
    {
        return $this->definitionsMap;
    }

    /**
     * Returns registered Service Providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }
}
