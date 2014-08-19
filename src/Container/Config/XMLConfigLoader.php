<?php

namespace Njasm\Container\Config;

use Njasm\Container\Config\ConfigLoader;
use Njasm\Container\ServicesProviderInterface;

class XMLConfigLoader implements ConfigLoader
{
    protected $filePath;
    
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }
    
    public function setConfig(ServicesProviderInterface $container)
    {
        $xmlContent = simplexml_load_file($this->filePath);
        
        foreach ($xmlContent->definitions->definition as $definition) {
            $definitionValues = $this->getDefinitionValues($definition);
            $this->setDefinition($definitionValues, $container);
        }
    }
    
    protected function getDefinitionValues(\SimpleXMLElement $definition)
    {
        $type = $this->getDefinitionType($definition);
        $key = $this->getDefinitionKey($definition);
        $value = $this->getDefinitionValue($definition);
        $singleton = $this->getDefinitionSingleton($definition);
        
        return array('type' => $type, 'key' => $key, 'value' => $value, 'singleton' => $singleton);
    }
    
    protected function getDefinitionType(\SimpleXMLElement $definition)
    {
        if (!isset($definition->attributes()->type)) {
            $this->raiseException("No type defined.");
        }
        
        return strtoupper((string) $definition->attributes()->type);
    }
    
    protected function getDefinitionKey(\SimpleXMLElement $definition)
    {
        if (!isset($definition->attributes()->key)) {
            $this->raiseException("No key defined.");
        }
        
        return (string) $definition->attributes()->key;
    }
    
    protected function getDefinitionValue(\SimpleXMLElement $definition)
    {
        if (!isset($definition->attributes()->value)) {
            $this->raiseException("No value defined.");
        }
        
        return (string) $definition->attributes()->value;
    }
    
    protected function getDefinitionSingleton(\SimpleXMLElement $definition)
    {
        if (!isset($definition->attributes()->singleton)) {
            return false;
        }
        
        return $definition->attributes()->singleton == "true" ? (bool) $definition->attributes()->singleton : false;
    }
    
    protected function setDefinition(array $definitionValues, ServicesProviderInterface $container)
    {
        if ($definitionValues['type'] === 'OBJECT') {
            $value = new $definitionValues['value'];
            $container->set($definitionValues['key'], $value);
            
            return;
        }
        
        if ($definitionValues['type' === 'SINGLETON']) {
            $value = new $definitionValues['value'];
            $container->singleton($definitionValues['key'], $value);
            
            return;
        }
        
        if ($definitionValues['type'] === 'PRIMITIVE') {
            $container->set($definitionValues['key'], $definitionValues['value']);
            
            return;
        }
    }
    
    protected function raiseException($message)
    {
        throw new \Exception($message);
    }
}
