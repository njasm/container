<?php

namespace Njasm\Container\Definition\Builder;

use Njasm\Container\Definition\Service\Request;

class ReflectionBuilder implements BuilderInterface
{
    public function execute(Request $request)
    {
        $key = $request->getKey();
        $container = $request->getContainer();
                
        $reflected = new \ReflectionClass($key);

        // abstract class or interface?
        if (!$reflected->isInstantiable()) {
            
            $message = "Unable to resolve [{$reflected->name}]";
            $this->raiseException($message);
            
        }
        
        $constructor = $reflected->getConstructor();  
        $parameters = array();
        
        if (!is_null($constructor)) {
                    
            foreach($constructor->getParameters() as $param) {          
                
                if(!$param->isDefaultValueAvailable()) {
                    
                        $dependency = $param->getClass();

                    if (!is_null($dependency)) {
                            
                        $parameters[] = $container->get($dependency->name);
   
                    } else {
                        
                        $message = "Unable to resolve [{$param->name}] in --{$param->getDeclaringClass()->getName()}";
                        $this->raiseException($message);
                        
                    }
                    
                } else {
                    
                    $parameters[] = $param->getDefaultValue();
                    
                }
            }
        }
        
        return !empty($parameters) ? $reflected->newInstanceArgs($parameters) : $reflected->newInstanceArgs();
    }
    
    protected function raiseException($message = null)
    {
        throw new \Exception($message);
    }
}
