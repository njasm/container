<?php

namespace Njasm\Container\Tests\Definition\Builder;

// HELPER CLASSES
class NoConstructArgs
{
    public $attribute;
    
    public function __construct()
    {
        $this->attribute = "NoConstructArgs";
    }
}

class ConstructArgsNull
{
    public $attribute;
    
    public function __construct($value = null)
    {
        $this->attribute = $value;
    }
}

class ConstructArgsString
{
    public $attribute;
    
    public function __construct($value = "test")
    {
        $this->attribute = $value;
    }
}

class ConstructArgsObject
{
    public $attribute;
    
    public function __construct(\SplObjectStorage $value)
    {
        $this->attribute = $value;
    }
}

class ConstructUnableResolve
{
    public $attribute;
    
    public function __construct(NonExistent $value)
    {
        $this->attribute = $value;
    }    
}

class VariableNoDefaultValue
{
    public $attribute;
    
    public function __construct($attribute)
    {
        $this->attribute = $attribute;
    }
}

// COMPLEX 

interface TestInterface
{
    public function get();
}

class ImplementsInterface implements TestInterface
{
    public function get()
    {
        return "test";
    }
}

class ComplexDependency
{
    public $defaultValue;
    public $resolvable;
    public $containerRegistered;
    public $interface;
    
    public function __construct(
        ConstructArgsString $resolvable,
        NoConstructArgs $containerRegistered,
        TestInterface $interface,
        $defaultValue = "Default-Value"
    ) {
        $this->defaultValue = $defaultValue;
        $this->resolvable = $resolvable;
        $this->containerRegistered = $containerRegistered;
        $this->interface = $interface;
    }
}
