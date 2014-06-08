<?php

namespace Njasm\ServicesContainer;

interface ServicesProviderInterface 
{
    /**
     * Check if service is registered.
     * 
     * @param   string  $service The service to check
     * @return  boolean true or false
     */
    public function has($service);
    
    /**
     * Return the requested service.
     * 
     * @param   string      $service The Servive to return
     * @return  Object      The Service
     * @throws  Exception   If service is not registered
     */
    public function get($service);
}
