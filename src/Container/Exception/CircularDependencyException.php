<?php
/**
 * Created by PhpStorm.
 * User: njasm
 * Date: 25/12/2017
 * Time: 01:13
 */

namespace Njasm\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class CircularDependencyException extends \Exception implements ContainerExceptionInterface
{

}