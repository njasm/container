<?php

namespace Njasm\Container\Tests\Definition;

use Njasm\Container\Definition\PrimitiveDefinition;
use Njasm\Container\Definition\DefinitionType;

class PrimitiveDefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
    }
    
    public function testGetType()
    {
        $d = new PrimitiveDefinition("primitive", array("a"), DefinitionType::PRIMITIVE);
        $this->assertTrue($d->getType() === DefinitionType::PRIMITIVE);
    }
    
    public function testGetKey()
    {
        $key = "primitive";
        $d = new PrimitiveDefinition($key, array("a"), DefinitionType::PRIMITIVE);
        $this->assertTrue($d->getKey() === $key);        
    }
    
    public function testGetDefinition()
    {
        $d = new PrimitiveDefinition("primitive", array("a"), DefinitionType::PRIMITIVE);
        $this->assertEquals(array("a"), $d->getConcrete());
    }
    
    public function testInvalidDefinitionType()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $d = new PrimitiveDefinition("primitive", new \stdClass(), DefinitionType::PRIMITIVE);
    }
    
    public function testInvalidDefinitionType2()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $d = new PrimitiveDefinition("primitive", function() { return "test"; }, DefinitionType::PRIMITIVE);
    }  
    
    public function testInvalidKey()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $d = new PrimitiveDefinition(null, "A", DefinitionType::PRIMITIVE);
    }
}
