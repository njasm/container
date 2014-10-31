<?php

namespace Njasm\Container\Tests\Definition\Service;

use Njasm\Container\Definition\Service\Request,
    Njasm\Container\Container, 
    Njasm\Container\Definition\DefinitionMap;


class RequestTest extends \PHPUnit_Framework_TestCase
{
    public $container;
    public $definitionsMap;
    public $providers;
    
    public function setUp()
    {
        $this->container        = new Container();
        $this->definitionsMap   = new DefinitionMap();
        $this->providers        = array();
    }
    
    public function testNullKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $request = $this->helperGetRequestObject(null);
    }
    
    public function testEmptyKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $request = $this->helperGetRequestObject("");        
    }
    
    public function testWhiteSpaceKey()
    {
        $this->setExpectedException('InvalidArgumentException');
        $request = $this->helperGetRequestObject("   ");        
    }
    
    public function testGet()
    {
        $request = $this->helperGetRequestObject("Test");
        
        $this->assertTrue("Test" === $request->getKey());
        $this->assertTrue($this->definitionsMap === $request->getDefinitionsMap());
        $this->assertTrue($this->container === $request->getContainer());
        $this->assertTrue($this->providers === $request->getProviders());
    }
    
    public function helperGetRequestObject($key = null)
    {
        return new Request($key, $this->definitionsMap, $this->providers, $this->container);
    }
}
