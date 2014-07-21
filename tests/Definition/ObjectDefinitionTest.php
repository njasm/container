<?php

namespace Njasm\Container\Tests\Definition;

use Njasm\Container\Definition\ObjectDefinition;
use Njasm\Container\Definition\DefinitionType;

class ObjectDefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
    }
    
    public function testException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $d = new ObjectDefinition("object", array());
    }
    
    public function testGetAll()
    {
        $d = new ObjectDefinition("object", new \stdClass());
        $this->assertTrue($d->getType() === DefinitionType::OBJECT);
        $this->assertInstanceOf('\stdClass', $d->getConcrete());
        $this->assertTrue($d->getKey() === 'object');
    }
    
    public function testInvalidKey()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $d = new ObjectDefinition(new \stdClass(), new \stdClass());        
    }    
}