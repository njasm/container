<?php

namespace Njasm\Container\Tests\Definition\Builder;

use Njasm\Container\Container;

include 'helperClasses.php';

class ReflectionBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $container;
    
    public function setUp()
    {
        $this->container = new Container();
    }
    
    public function testReflection()
    {
        $returnValue = $this->container->get('\Njasm\Container\Tests\Definition\Builder\NoConstructArgs');
        
        $this->assertInstanceOf('\Njasm\Container\Tests\Definition\Builder\NoConstructArgs', $returnValue);  
    }
    
    public function testArgsNull()
    {
        $returnValue = $this->container->get('\Njasm\Container\Tests\Definition\Builder\ConstructArgsNull');
        
        $this->assertInstanceOf('\Njasm\Container\Tests\Definition\Builder\ConstructArgsNull', $returnValue);
    }
    
    public function testArgsString()
    {
        $returnValue = $this->container->get('\Njasm\Container\Tests\Definition\Builder\ConstructArgsString');
        
        $this->assertInstanceOf('\Njasm\Container\Tests\Definition\Builder\ConstructArgsString', $returnValue);
        $this->assertEquals("test", $returnValue->attribute);
    }
    
    public function testArgsObject()
    {
        $returnValue = $this->container->get('\Njasm\Container\Tests\Definition\Builder\ConstructArgsObject');
        
        $this->assertInstanceOf('\Njasm\Container\Tests\Definition\Builder\ConstructArgsObject', $returnValue);
        $this->assertInstanceOf('\SplObjectStorage', $returnValue->attribute);
    }
    
    public function testUnresolvable()
    {
        $this->setExpectedException('\Exception');
        $returnValue = $this->container->get('\Njasm\Container\Tests\Definition\Builder\ConstructUnableResolve');
    }
    
    public function testComplex()
    {
        $this->container->set(
            'Njasm\Container\Tests\Definition\Builder\TestInterface',
            function() {
                return new \Njasm\Container\Tests\Definition\Builder\ImplementsInterface();
            }
        );
        
        $this->container->set('NoConstructArgs', new NoConstructArgs());  
        $returnValue = $this->container->get('Njasm\Container\Tests\Definition\Builder\ComplexDependency');
        
        $this->assertInstanceOf('Njasm\Container\Tests\Definition\Builder\ComplexDependency', $returnValue);
        $this->assertInstanceOf(
            'Njasm\Container\Tests\Definition\Builder\ConstructArgsString', 
            $returnValue->resolvable
        );
        $this->assertInstanceOf(
            'Njasm\Container\Tests\Definition\Builder\NoConstructArgs', 
            $returnValue->containerRegistered
        );
        $this->assertInstanceOf(
            'Njasm\Container\Tests\Definition\Builder\TestInterface',
            $returnValue->interface
        );
        $this->assertInstanceOf(
            'Njasm\Container\Tests\Definition\Builder\ImplementsInterface',
            $returnValue->interface
        );
        $this->assertEquals('Default-Value', $returnValue->defaultValue);
        
        $returnValue2 = $this->container->get('Njasm\Container\Tests\Definition\Builder\ComplexDependency');
        
        $this->assertEquals($returnValue2, $returnValue);        
    }

    public function testComplexUnresolvableInterface()
    {
        // missing interface binding, will throw exception
        $this->setExpectedException('\Exception');
        
        $this->container->set('Njasm\Container\Tests\Definition\Builder\NoConstructArgs', new NoConstructArgs());  
        $this->container->get('Njasm\Container\Tests\Definition\Builder\ComplexDependency');
    }
    
    public function testVariableNoDefaultValue()
    {
        $this->setExpectedException('\Exception');
        $returnValue = $this->container->get('\Njasm\Container\Tests\Definition\Builder\VariableNoDefaultValue');
    }    
}
