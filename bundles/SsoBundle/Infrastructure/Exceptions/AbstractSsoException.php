<?php


namespace SsoBundle\Infrastructure\Exceptions;


use Exception;

abstract class AbstractSsoException extends Exception
{
    abstract function toArray();
}