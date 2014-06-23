<?php

namespace Njasm\ServicesContainer\Storage;

use \Njasm\ServicesContainer\ServicesProviderInterface;

interface StorageInterface 
{
    public function has($service);
    public function set($service, $value);
    public function singleton($service, \Closure $value);
    public function provider(ServicesProviderInterface $provider);
    public function get($service);
    public function remove($service);
    public function reset();
}
