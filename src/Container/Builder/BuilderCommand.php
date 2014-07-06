<?php

namespace Njasm\Container\Builder;

use Njasm\Container\Definition\AbstractDefinition;

interface BuilderCommand
{
    public function execute(AbstractDefinition $definition);
}
