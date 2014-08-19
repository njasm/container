<?php

namespace Njasm\Container\Tests\Config;

use Njasm\Container\Container;
use Njasm\Container\Config\XMLConfigLoader;

class XMLConfigLoaderTest extends \PHPUnit_Framework_TestCase 
{
    protected $container;
    
    public function setUp()
    {
        $loader = new XMLConfigLoader(__DIR__ . DIRECTORY_SEPARATOR . 'XMLConfig.xml');
        $this->container = new Container($loader);
    }
    
    public function testObjectDefinition()
    {
       $key = "NoConstructArgs";
       $instance = 'Njasm\Container\Tests\Definition\Builder\NoConstructArgs';
       
       $returnValue = $this->container->get($key);
       $this->assertInstanceOf($instance, $returnValue);
    }
    
    public function testSingletonDefinition()
    {
       $key = "ConstructArgsNull";
       $instance = 'Njasm\Container\Tests\Definition\Builder\ConstructArgsNull';
       
       $returnValue = $this->container->get($key);
       $this->assertInstanceOf($instance, $returnValue);        
    }
    
    public function testPrimitiveDefinition()
    {
       $key = "Username";
       $value = 'John Doe';
       
       $returnValue = $this->container->get($key);
       $this->assertEquals($value, $returnValue);           
    }
    
    public function testReflectionDefinition()
    {
        // this type of definition is useless for the ConfigLoader, but container should be able to resolve
        // the unregistered service as the service dependencies anyway.   
        $key = "Njasm\Container\Tests\Definition\Builder\ConstructArgsObject";
        
        $returnValue = $this->container->get($key);
        $this->assertInstanceOf($key, $returnValue);
    }
}
