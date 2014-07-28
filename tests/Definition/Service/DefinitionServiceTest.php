<?php

namespace Njasm\Container\Tests\Definition\Service;

use Njasm\Container\Definition\Service\DefinitionService,
    Njasm\Container\Definition\DefinitionType,
    Njasm\Container\Definition\DefinitionsMap,
    Njasm\Container\Definition\Definition,
    Njasm\Container\Definition\Service\Request;

class DefinitionServiceTest extends \PHPUnit_Framework_TestCase
{
    public $service;
    
    public function setUp()
    {
        $this->service = new DefinitionService();
    }
    
    public function testAssembleException()
    {
        $this->setExpectedException('OutOfBoundsException');
        $this->service->assemble("test-key", null);
    }
}