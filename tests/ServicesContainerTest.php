<?php

namespace Njasm\ServicesContainer\Tests;

class ServicesContainerTest extends \PHPUnit_Framework_TestCase 
{
    private $container;
    
    public function setUp()
    {
        $this->container = new \Njasm\ServicesContainer\ServicesContainer();
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
        $provider = new \Njasm\ServicesContainer\ServicesContainer();
        
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