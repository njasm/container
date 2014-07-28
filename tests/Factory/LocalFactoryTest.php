<?php

namespace Njasm\Container\Tests\Factory;

use Njasm\Container\Factory\LocalFactory,
    Njasm\Container\Definition\DefinitionType,
    Njasm\Container\Definition\DefinitionsMap,
    Njasm\Container\Definition\Definition,
    Njasm\Container\Definition\Service\Request;

class LocalFactoryTest extends \PHPUnit_Framework_TestCase
{
    public $factory;
    
    public function setUp()
    {
        $this->factory = new LocalFactory();
    }
    
    public function testException()
    {
        $definitionType = new DefinitionType(10);
        $definition = new Definition("TestException", null, $definitionType);
        $definitionsMap = new DefinitionsMap(array());
        $providers = new \SplObjectStorage();
        
        $definitionsMap->add($definition);
        $container = $this->getMock('Njasm\Container\ServicesProviderInterface');
        $request = new Request("TestException", $definitionsMap, $providers, $container);
        
        $this->setExpectedException('OutOfBoundsException');
        $this->factory->build($request);
    }
}