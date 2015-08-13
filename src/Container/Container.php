<?php

namespace Njasm\Container;

use Njasm\Container\Definition\DefinitionsMap;
use Njasm\Container\Definition\Service\DefinitionService;
use Njasm\Container\Definition\Service\DependencyBag;
use Njasm\Container\Definition\Service\Request;
use Njasm\Container\Exception\NotFoundException;

class Container implements ServicesProviderInterface
{
    protected $definitionsMap;
    protected $providers;
    protected $registry;
    protected $singletons;
    protected $service;

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
        $this->definitionsMap = new DefinitionsMap();
        $this->registry = array();
        $this->singletons = array();

        $this->service = new DefinitionService();

        // register Container
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
        return $this->service->has($this->getRequest($key));
    }

    /**
     * Register a new service in the container.
     *
     * @param   string      $key
     * @param   mixed       $concrete
     * @param   array       $construct
     * @param   array       $properties
     * @param   array       $methods
     * @return  Definition\Definition
     */
    public function set(
        $key,
        $concrete,
        array $construct = array(),
        array $properties = array(),
        array $methods = array()
    ) {
        $dependencyBag = new DependencyBag($construct, $properties, $methods);
        $definition = $this->service->assemble($key, $concrete, $dependencyBag);
        $this->definitionsMap->add($definition);

        $definitionType = $definition->getType();

        if (
            $definitionType === Definition\DefinitionType::OBJECT
            || $definitionType === Definition\DefinitionType::PRIMITIVE
        ) {
            $this->registerSingleton($key);
        }

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
        $definition = $this->service->assembleAliasDefinition($alias, $key);
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
        $definition = $this->service->assembleBindDefinition($key, $concrete, $dependencyBag);
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
        $this->registerSingleton($key);

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
        $this->registerSingleton($key);

        return $definition;
    }

    /**
     * Register a service key as singleton.
     *
     * @param   string      $key
     * @return  Container
     */
    protected function registerSingleton($key)
    {
        $this->singletons[$key] = true;

        return $this;
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
        $request = $this->getRequest($key, $dependencyBag);
        $returnValue = $this->service->build($request);
        $this->injectValues($returnValue, $request);

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
     * Build a new Request value object.
     *
     * @param   string              $key
     * @param   null|DependencyBag       $dependencyBag
     * @return  Request
     */
    protected function getRequest($key, DependencyBag $dependencyBag = null)
    {
        return new Request($key, $this->definitionsMap, $this->providers, $this, $dependencyBag);
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
     * Inject properties and call methods on Objects already instantiated.
     *
     * @param   Object  $concrete
     * @param   Request $request
     * @return  mixed
     */
    public function injectValues($concrete, Request $request)
    {
        if (is_object($concrete) && !$concrete instanceof \Closure) {
            $this->injectParams($concrete, $request);
            $this->callMethods($concrete, $request);
        }

        return $concrete;
    }
}
