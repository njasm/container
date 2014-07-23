<?php

namespace Njasm\Container\Tests\Definition;

use Njasm\Container\Definition\ClosureDefinition;
use Njasm\Container\Definition\DefinitionType;

class ClosureDefinitionTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
    }
    
    public function testException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $d = new ClosureDefinition("closure", new \stdClass(), DefinitionType::CLOSURE);
    }
    
    public function testGetAll()
    {
        $anonymous = function() { return "A"; };
        
        $d = new ClosureDefinition("closure", $anonymous, DefinitionType::CLOSURE);
        $this->assertTrue($d->getType() === DefinitionType::CLOSURE);
        $this->assertEquals($anonymous, $d->getConcrete());
        $this->assertTrue($d->getKey() === 'closure');        
    }
    
    public function testInvalidKey()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $d = new ClosureDefinition("", function() { return "test"; }, DefinitionType::CLOSURE);        
    }    
}
