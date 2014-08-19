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
        
    }
    
    public function testPrimitiveDefinition()
    {
        
    }
}
