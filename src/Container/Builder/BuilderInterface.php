<?php

namespace Njasm\Container\Builder;

use Njasm\Container\Definition\AbstractDefinition;

interface BuilderInterface
{
    public function execute(AbstractDefinition $definition);
}
