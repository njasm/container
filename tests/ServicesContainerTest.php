<?php

namespace Njasm\ServicesContainer\Tests;

class ServicesContainerTest extends \PHPUnit_Framework_TestCase 
{
    private $container;
    
    public function setUp()
    {
        $this->container = new \Njasm\ServicesContainer\ServicesContainer(
            new \Njasm\ServicesContainer\Storage\InMemoryStorage()
        );
    }
    
    public function testHas()
    {
        $this->setService();
        $this->assertTrue($this->container->has("SingleClass"));
        $this->assertFalse($this->container->has("Non-Existent-Service"));
    }
    
    public function testGetException()
    {
        $this->setExpectedException('\Njasm\ServicesContainer\Exception\ServiceNotRegisteredException');
        $this->container->get("Non-existent-service");
    }
    
    public function testSetAndGet()
    {
        $this->setService();
        
        $object = $this->container->get("SingleClass");
        $this->assertInstanceOf("Njasm\\ServicesContainer\\Tests\\SingleClass", $object);
    }
    
    public function testSingletonAndGet()
    {
        $this->setService("singleton");
        
        $obj1 = $this->container->get("SingleClass");
        $obj2 = $this->container->get("SingleClass");
        
        $this->assertInstanceOf("Njasm\\ServicesContainer\\Tests\\SingleClass", $obj1);
        $this->assertInstanceOf("Njasm\\ServicesContainer\\Tests\\SingleClass", $obj2);
        
        // exactly the same object
        $obj1->value = "SingleTone-Test";
        $this->assertEquals($obj1->value, $obj2->value);
    }
    
    public function testServiceFromProvider()
    {
        $provider = $this->getServiceProvider();
        $this->container->provider($provider);
        
        $obj = $this->container->get("SingleClassOnServiceProvider");
        $this->assertInstanceOf("Njasm\\ServicesContainer\\Tests\\SingleClassOnServiceProvider", $obj);
        $this->assertEquals("Object-from-Service-Provider", $obj->value);
    }
    
    public function testNestedDependency()
    {
        $container = &$this->container;
        
        $this->setService("set", "SingleClass");
        
        $this->container->set(
            "DependentClass",
            function () use (&$container) {
                return new DependentClass($container->get("SingleClass"));
            }
        );
        
        $dependent = $this->container->get("DependentClass");
        $this->assertInstanceOf("Njasm\\ServicesContainer\\Tests\\DependentClass", $dependent);
        $this->assertInstanceOf("Njasm\\ServicesContainer\\Tests\\SingleClass", $dependent->getInjectedClass());
    }
    
    public function testNestedDependencyWithSingleton()
    {
        $container = &$this->container;
        
        $this->setService("singleton", "SingleClass");
        
        $this->container->set(
            "DependentClass",
            function () use (&$container) {
                return new DependentClass($container->get("SingleClass"));
            }
        );
        
        //get sigleton first
        $singleton = $this->container->get("SingleClass");
        // now get a dependent go get the singleton and compare if it's the same object and not a new instance
        $dependent = $this->container->get("DependentClass");
        $this->assertInstanceOf("Njasm\\ServicesContainer\\Tests\\DependentClass", $dependent);
        $this->assertInstanceOf("Njasm\\ServicesContainer\\Tests\\SingleClass", $dependent->getInjectedClass()); 
        
        $this->assertTrue($singleton === $dependent->getInjectedClass());
    }
    
    public function testSetPrimitiveDataTypeAsService()
    {
        $expectedString = "VariableString";
        $expectedBool = true;
        $expectedInt = 123;
        $expectedFloat = 45.678;
        
        $this->container->set("string", $expectedString);
        $this->container->set("bool", $expectedBool);
        $this->container->set("int", $expectedInt);
        $this->container->set("float", $expectedFloat);
        
        $this->assertTrue($expectedString === $this->container->get("string"));
        $this->assertTrue($expectedBool === $this->container->get("bool"));
        $this->assertTrue($expectedInt === $this->container->get("int"));
        $this->assertTrue($expectedFloat === $this->container->get("float")); 
    }
    
    public function testInstanciatedObject()
    {
        $obj = new \stdClass();
        $this->container->set("obj", $obj);
        
        $objResult1 = $this->container->get("obj");
        $objResult2 = $this->container->get("obj");
        
        $this->assertTrue($objResult1 === $objResult2);
    }
    
    
    public function testRemove()
    {
        $obj = new \stdClass();
        $this->container->set("obj1", $obj);
        $this->container->set("obj2", $obj);
        
        
        $remove1 = $this->container->remove("obj1");
        $remove2 = $this->container->remove("obj2");
        
        $this->assertTrue($remove1 === true);
        $this->assertTrue($remove2 === true);
    }
    
    
    
    /** HELPER METHODS **/
    protected function setService($methodType = "set", $service = "SingleClass")
    {
        $this->container->{$methodType}(
            $service,
            function() {
                return new SingleClass();
            }
        );        
    }
    
    protected function getServiceProvider()
    {
        $provider = new \Njasm\ServicesContainer\ServicesContainer(
            new \Njasm\ServicesContainer\Storage\InMemoryStorage()
        );
        
        $provider->set(
            "SingleClassOnServiceProvider",
            function() {
                return new SingleClassOnServiceProvider();
            }
        );  
        
        return $provider;
    }
}



/** TESTING CLASSES **/
class SingleClass
{
    public $value;
}

class SingleClassOnServiceProvider
{
    public $value = "Object-from-Service-Provider";
}

class DependentClass
{
    public $single;
    
    public function __construct(SingleClass $single)
    {
        $this->single = $single;
    }
    
    public function getInjectedClass()
    {
        return $this->single;
    }
}