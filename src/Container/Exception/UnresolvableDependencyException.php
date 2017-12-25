<?php
/**
 * Created by PhpStorm.
 * User: njasm
 * Date: 25/12/2017
 * Time: 01:15
 */

namespace Njasm\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class UnresolvableDependencyException extends \Exception implements ContainerExceptionInterface
{

}