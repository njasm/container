<?php

namespace Njasm\Container;

use Njasm\Container\Definition\AliasDefinition;
use Njasm\Container\Definition\BindDefinition;
use Njasm\Container\Definition\ClosureDefinition;
use Njasm\Container\Definition\ObjectDefinition;
use Njasm\Container\Definition\ProviderDefinition;
use Njasm\Container\Definition\ValueDefinition;
use Njasm\Container\Exception\NotFoundException;
use Njasm\Container\Exception\ContainerException;

class Container implements ServicesProviderInterface
{
    /**
     * @var array
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
        $this->definitionsMap = array();
        
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
        if (isset($this->definitionsMap[$key])) {
            return true;
        }

        return $this->providersHas($key);
    }

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

        if ($concrete instanceof \Closure) {
            $definition = new ClosureDefinition($key, $concrete, $this, $construct, $properties, $methods);
        } elseif (is_object($concrete)) {
            $definition = new ObjectDefinition($key, $concrete, $this, $properties, $methods);
        } else {
            $definition = new ValueDefinition($key, $concrete, $this, $construct, $properties, $methods);
            $this->singletons[$key] = true;
        }

        $this->definitionsMap[$key] = $definition;

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
        $definition = new AliasDefinition($alias, $key, $this, array(), array(), array());
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
     * @return  Definition\Definition
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
        } catch (\Exception $e) {
            throw $e;
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

        // circular dependency guard
        if (array_key_exists($key, $this->buildingKeys)) {
            throw new ContainerException("Circular Dependency detected for {$key}");
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
            $definition = new BindDefinition($key, $key, $this);
            $request->getDefinitionsMap()->add($definition);
        }

        $returnValue = $this->factory->build($request);
        unset($this->buildingKeys[$key]);

        return $returnValue;
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
