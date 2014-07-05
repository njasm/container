<?php

namespace Njasm\Container\Tests;


class ContainerTest extends \PHPUnit_Framework_TestCase 
{
    private $container;
    
    public function setUp()
    {
        $this->container = new \Njasm\Container\Container();
    }

    // TODO: validade $value of Definition for null/empty
    public function testHas()
    {
        $p = $this->getPrimitiveDef();
        $this->container->set($p);
        
        $this->assertTrue($this->container->has($p->getKey()));
        $this->assertFalse($this->container->has("Non-Existent-Service"));
    }
    
    public function testGetException()
    {
        $this->setExpectedException('\Njasm\Container\Exception\NotFoundException');
        $this->container->get("Non-existent-service");
    }
    
    public function testSetAndGet()
    {
        $o = $this->getSingleClassObjectDef();
        $this->container->set($o);
        
        $object = $this->container->get("SingleClass");
        $this->assertInstanceOf("Njasm\\Container\\Tests\\SingleClass", $object);
    }
    
    public function testSingletonAndGet()
    {
        $o = $this->getSingleClassObjectDef();
        $this->container->singleton($o);
        
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
        
        $o = $this->getSingleClassObjectDef();
        $d = new \Njasm\Container\Definition\FactoryDefinition(
            "DependentClass",
            function () use (&$container) {
                return new DependentClass($container->get("SingleClass"));
            }
        );
            
        $this->container->set($d);
        $this->container->set($o);
        
        $dependent = $this->container->get("DependentClass");
        $this->assertInstanceOf("Njasm\\Container\\Tests\\DependentClass", $dependent);
        $this->assertInstanceOf("Njasm\\Container\\Tests\\SingleClass", $dependent->getInjectedClass());
    }
    
    public function testNestedDependencyWithSingleton()
    {
        $container = &$this->container;
        
        $o = $this->getSingleClassObjectDef();
        $d = new \Njasm\Container\Definition\FactoryDefinition(
            "DependentClass",
            function () use (&$container) {
                return new DependentClass($container->get("SingleClass"));
            }
        );
            
        $this->container->singleton($d);
        $this->container->set($o);
        
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
        $expectedString = $this->getPrimitiveDef("string", "VariableString");
        $expectedBool = $this->getPrimitiveDef("bool", true);
        $expectedInt = $this->getPrimitiveDef("int", 123);
        $expectedFloat = $this->getPrimitiveDef("float", 45.678);
        
        $this->container->set($expectedString);
        $this->container->set($expectedBool);
        $this->container->set($expectedInt);
        $this->container->set($expectedFloat);
        
        $this->assertTrue("VariableString" === $this->container->get("string"));
        $this->assertTrue(true === $this->container->get("bool"));
        $this->assertTrue(123 === $this->container->get("int"));
        $this->assertTrue(45.678 === $this->container->get("float")); 
    }
    
    public function testInstanciatedObject()
    {
        $obj = $this->getSingleClassObjectDef("SingleClass");
        $this->container->set($obj);
        
        $objResult1 = $this->container->get("SingleClass");
        $objResult2 = $this->container->get("SingleClass");
        
        $this->assertTrue($objResult1 === $objResult2);
    }
        
    public function testRemove()
    {
        $obj = new \stdClass();
        $this->container->set(new \Njasm\Container\Definition\ObjectDefinition("obj1", $obj));
        $this->container->set(new \Njasm\Container\Definition\ObjectDefinition("obj2", $obj));
        
        
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
        $d = $this->getSingleClassObjectDef("SingleClass");
        $this->container->singleton($d);
        
        $obj = $this->container->get("SingleClass");
        
        $this->assertInstanceOf("\\Njasm\Container\\Tests\\SingleClass", $obj);
        
        $result = $this->container->remove("SingleClass");
        $this->assertTrue($result);
    }
    
    public function testReset()
    {
        $d = $this->getSingleClassObjectDef("SingleClass");
        $this->container->set($d);
        
        $this->assertTrue($this->container->has("SingleClass"));
        $this->assertTrue($this->container->reset());
        
        $this->setExpectedException('\Njasm\Container\Exception\NotFoundException');
        $this->container->get("SingleClass");
    }
    
    public function testResetSingleton()
    {
        $d = $this->getSingleClassObjectDef("SingleClass");
        $this->container->singleton($d);
        
        $this->assertTrue($this->container->has("SingleClass"));
        $this->assertTrue($this->container->reset());
        
        $this->setExpectedException('\Njasm\Container\Exception\NotFoundException');
        $this->container->get("SingleClass");
    }    
    
    /** HELPER METHODS **/
    protected function getPrimitiveDef($key = "primitive", $value = "primitive-def")
    {
        return new \Njasm\Container\Definition\PrimitiveDefinition($key, $value);
    }
    
    protected function getSingleClassObjectDef($key = "SingleClass")
    {
        $value = new SingleClass();
            
        return new \Njasm\Container\Definition\ObjectDefinition($key, $value);
    }
    
    protected function getServiceProvider($key = "SingleClassOnServiceProvider")
    {
        $provider = new \Njasm\Container\Container();
        
        $d = new \Njasm\Container\Definition\FactoryDefinition(
            $key,
            function() {
                return new SingleClassOnServiceProvider();
            }
        );
        
        $provider->set($d);
        
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