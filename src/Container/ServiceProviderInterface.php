<?php
/**
 * Created by PhpStorm.
 * User: njasm
 * Date: 24/12/2017
 * Time: 04:00
 */

namespace Njasm\Container;

use Njasm\Container\Definition\AbstractDefinition;
use Psr\Container\ContainerInterface as PsrContainer;

interface ServiceProviderInterface extends PsrContainer
{

    /**
     * Register a new service in the container.
     *
     * @param   string      $key
     * @param   mixed       $concrete
     * @param   array       $construct
     * @param   array       $properties
     * @param   array       $methods
     *
     * @return  AbstractDefinition
     */
    public function set(
        string $key, $concrete, array $construct = [], array $properties = [], array $methods = array()
    ) : AbstractDefinition;

    /**
     * Removes a service from the container.
     * This will NOT remove services from other nested providers.
     *
     * @param   string  $key
     * @return  boolean
     */
    public function remove($key) : bool;

    /**
     * Register an alias to a service key.
     *
     * @param   string      $alias
     * @param   string      $key
     * @return  Definition\AliasDefinition
     */
    public function alias(string $alias, string $key) : AbstractDefinition;

    /**
     * Returns registered Service Providers.
     *
     * @return PsrContainer[]
     */
    public function getProviders() : array;

    /**
     * Reset, clear container registered definitions and objects.
     *
     * @return  void
     */
    public function reset() : void;
}