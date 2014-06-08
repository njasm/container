<?php

namespace Njasm\ServicesContainer;

interface ServicesProviderInterface 
{
    /**
     * Check if service is registered.
     * 
     * @param   string  $service the service to check
     * @return  boolean
     */
    public function has($service);
    
    /**
     * Return the requested service instanciated.
     * 
     * @param   string      $service The Servive to return
     * @return  Object
     * 
     * @throws  Exception   If service is not registered
     */
    public function get($service);
}
