<?php

namespace Njasm\Container\Config;

use Njasm\Container\ServicesProviderInterface;

interface ConfigLoader
{
    public function setConfig(ServicesProviderInterface $container);
}

