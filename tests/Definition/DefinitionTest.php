<?php

namespace Njasm\Container\Tests\Definition;

use Njasm\Container\Container;
use Njasm\Container\Definition\Definition;
use Njasm\Container\Definition\DefinitionType;

class DefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }
    
    public function testGetType()
    {
        $d = new Definition("primitive", array("a"), new DefinitionType(DefinitionType::PRIMITIVE));
        $this->assertTrue($d->getType() === DefinitionType::PRIMITIVE);
    }
    
    public function testGetKey()
    {
        $key = "primitive";
        $d = new Definition($key, array("a"),  new DefinitionType(DefinitionType::PRIMITIVE));
        $this->assertTrue($d->getKey() === $key);        
    }
    
    public function testGetDefinition()
    {
        $d = new Definition("primitive", array("a"),  new DefinitionType(DefinitionType::PRIMITIVE));
        $this->assertEquals(array("a"), $d->getConcrete());
    }
    
    public function testInvalidKey()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $d = new Definition(null, "A",  new DefinitionType(DefinitionType::PRIMITIVE));
    }
}
