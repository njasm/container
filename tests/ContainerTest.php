<?php

namespace Njasm\Container\Tests;


class ContainerTest extends \PHPUnit_Framework_TestCase 
{
    private $container;
    
    public function setUp()
    {
        $this->container = new \Njasm\Container\Container();
    }

    public function testHas()
    {
        $this->container->set("primitive", "primitive-def");
        
        $this->assertTrue($this->container->has("primitive"));
        $this->assertFalse($this->container->has("Non-Existent-Service"));
    }
    
    public function testGetException()
    {
        $this->setExpectedException('\Njasm\Container\Exception\NotFoundException');
        $this->container->get("Non-existent-service");
    }
    
    public function testSetAndGet()
    {
        $this->container->set("SingleClass", new SingleClass());
        
        $object = $this->container->get("SingleClass");
        $this->assertInstanceOf("Njasm\\Container\\Tests\\SingleClass", $object);
    }
    
    public function testSingletonAndGet()
    {
        $this->container->singleton("SingleClass", new SingleClass());
        
        $obj1 = $this->container->get("SingleClass");
        $obj2 = $this->container->get("SingleClass");
        
        $this->assertInstanceOf("Njasm\\Container\\Tests\\SingleClass", $obj1);
        $this->assertInstanceOf("Njasm\\Container\\Tests\\SingleClass", $obj2);
        
        // exactly the same object
        $obj1->value = "SingleTone-Test";
        $this->assertEquals($obj1->value, $obj2->value);
    }
    
    public function testServiceFromProvider()
    {
        $provider = $this->getServiceProvider("SingleClassOnServiceProvider");
        $this->container->provider($provider);

        //test has from provider
        $this->assertTrue($this->container->has("SingleClassOnServiceProvider"));
        // test get from provider
        $obj = $this->container->get("SingleClassOnServiceProvider");
        $this->assertInstanceOf("Njasm\\Container\\Tests\\SingleClassOnServiceProvider", $obj);
        $this->assertEquals("Object-from-Service-Provider", $obj->value);
    }
    
    public function testNestedDependency()
    {
        $container = &$this->container;
        
        $this->container->set(
            "DependentClass",
            function () use (&$container) {
                return new DependentClass($container->get("SingleClass"));
            }            
        );
        
        $this->container->set("SingleClass", new SingleClass());
        
        $dependent = $this->container->get("DependentClass");
        $this->assertInstanceOf("Njasm\\Container\\Tests\\DependentClass", $dependent);
        $this->assertInstanceOf("Njasm\\Container\\Tests\\SingleClass", $dependent->getInjectedClass());
    }
    
    public function testNestedDependencyWithSingleton()
    {
        $container = &$this->container;
            
        $this->container->singleton(
            "DependentClass",
            function () use (&$container) {
                return new DependentClass($container->get("SingleClass"));
            }
        );
        
        $this->container->set("SingleClass", new SingleClass());
        
        //get sigleton first
        $singleton = $this->container->get("SingleClass");
        // now get a dependent go get the singleton and compare if it's the same object and not a new instance
        $dependent = $this->container->get("DependentClass");
        $this->assertInstanceOf("Njasm\\Container\\Tests\\DependentClass", $dependent);
        $this->assertInstanceOf("Njasm\\Container\\Tests\\SingleClass", $dependent->getInjectedClass()); 
        
        $this->assertTrue($singleton === $dependent->getInjectedClass());
    }
    
    public function testSetPrimitiveDataTypeAsService()
    {
        $this->container->set("string", "VariableString");
        $this->container->set("bool", true);
        $this->container->set("int", 123);
        $this->container->set("float", 45.678);
        
        $this->assertTrue("VariableString" === $this->container->get("string"));
        $this->assertTrue(true === $this->container->get("bool"));
        $this->assertTrue(123 === $this->container->get("int"));
        $this->assertTrue(45.678 === $this->container->get("float")); 
    }
    
    public function testInstanciatedObject()
    {

        $this->container->set("SingleClass", new SingleClass());
        
        $objResult1 = $this->container->get("SingleClass");
        $objResult2 = $this->container->get("SingleClass");
        
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
    
    public function testRemoveNotRegistered()
    {
        $result = $this->container->remove("No-Service-Registered");
        $this->assertFalse($result);
    }
    
    public function testRemoveSingleton()
    {
        $this->container->singleton("SingleClass", new SingleClass());
        
        $obj = $this->container->get("SingleClass");
        
        $this->assertInstanceOf("\\Njasm\Container\\Tests\\SingleClass", $obj);
        
        $result = $this->container->remove("SingleClass");
        $this->assertTrue($result);
    }
    
    public function testReset()
    {
        $this->container->set("SingleClass", new SingleClass());
        
        $this->assertTrue($this->container->has("SingleClass"));
        $this->assertTrue($this->container->reset());
        
        $this->setExpectedException('\Njasm\Container\Exception\NotFoundException');
        $this->container->get("SingleClass");
    }
    
    public function testResetSingleton()
    {
        $this->container->singleton("SingleClass", new singleClass());
        
        $this->assertTrue($this->container->has("SingleClass"));
        $this->assertTrue($this->container->reset());
        
        $this->setExpectedException('\Njasm\Container\Exception\NotFoundException');
        $this->container->get("SingleClass");
    }    
    
    /** HELPER METHODS **/
    
    protected function getSingleClassObjectDef($key = "SingleClass")
    {
        $value = new SingleClass();
            
        return new \Njasm\Container\Definition\ObjectDefinition($key, $value);
    }
    
    protected function getServiceProvider($key = "SingleClassOnServiceProvider")
    {
        $provider = new \Njasm\Container\Container();
        
        $provider->set(
            $key,
            function() {
                return new SingleClassOnServiceProvider();
            }            
        );
        
        return $provider;
    }
}

/** HELPER TEST CLASSES **/
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