<?php

namespace Njasm\Container\Definition\Service;

use Njasm\Container\Definition\Definition;
use Njasm\Container\Definition\DefinitionType;
use Njasm\Container\Definition\Finder\DefinitionFinder;
use Njasm\Container\Exception\ContainerException;
use Njasm\Container\Factory\DefinitionFactory;

class DefinitionService
{
    /**
     * @var DefinitionFinder
     */
    protected $definitionFinder;

    /**
     * @var DefinitionFactory
     */
    protected $factory;

    /**
     * @var array Service keys being built.
     */
    protected $buildingKeys = array();

    public function __construct()
    {
        $this->definitionFinder = new DefinitionFinder();
        $this->factory = new DefinitionFactory();
    }

    /**
     * Finds if a service is defined globally. Globally means, in this Container or in a nested Container.
     *
     * @param \Njasm\Container\Definition\Service\Request $request
     * @return boolean
     */
    public function has(Request $request)
    {
        return $this->definitionFinder->has($request);
    }

    /**
     * Assembles a Definition object based on the concrete value supplied.
     *
     * @param   string      $key
     * @param   \Closure    $concrete
     * @param   null|\Njasm\Container\Definition\Service\DependencyBag   $dependencyBag
     * @return  \Njasm\Container\Definition\Definition
     * @throws  \OutOfBoundsException
     */
    public function assemble($key, $concrete, DependencyBag $dependencyBag = null)
    {
        $definitionType = null;

        if ($concrete instanceof \Closure) {
            $definitionType = DefinitionType::CLOSURE;
        } elseif (is_object($concrete)) {
            $definitionType = DefinitionType::OBJECT;
        } elseif (is_scalar($concrete)) {
            $definitionType = DefinitionType::PRIMITIVE;
        }

        if (is_null($definitionType)) {
            throw new \OutOfBoundsException("Unknown definition type.");
        }

        return new Definition($key, $concrete, $definitionType, $dependencyBag);
    }

    /**
     * Assembles a Definition object of type Alias.
     *
     * @param   string      $key
     * @param   string      $concrete
     * @return  \Njasm\Container\Definition\Definition
     */
    public function assembleAliasDefinition($key, $concrete)
    {
        return new Definition($key, $concrete, DefinitionType::ALIAS);
    }

    /**
     * Assembles a Definition object of type Bind.
     *
     * @param   string      $key
     * @param   string      $concrete
     * @param   null|\Njasm\Container\Definition\Service\DependencyBag   $dependencyBag
     * @return  \Njasm\Container\Definition\Definition
     */
    public function assembleBindDefinition($key, $concrete, DependencyBag $dependencyBag = null)
    {
        return new Definition($key, $concrete, DefinitionType::REFLECTION, $dependencyBag);
    }

    /**
     * Build the requested service.
     *
     * @param   Request     $request
     * @return  mixed
     */
    public function build(Request $request)
    {
        $key = $request->getKey();

        // circular dependency guard
        if (array_key_exists($key, $this->buildingKeys)) {
            throw new ContainerException("Circular Dependency detected for {$key}");
        }

        $this->buildingKeys[$key] = true;

        if (!$this->definitionFinder->has($request)) {
            // try to bail-out client called service. We'll assemble a new reflection definition and will,
            // if class exists, try to resolve all dependencies and instantiate the object if possible.
            $def = $this->assembleBindDefinition($key, $key);
            $request->getDefinitionsMap()->add($def);
        }

        $returnValue = $this->factory->build($request);
        unset($this->buildingKeys[$key]);

        return $returnValue;
    }
}
