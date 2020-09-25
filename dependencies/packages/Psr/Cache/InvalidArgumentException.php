<?php

namespace ROP\Dependencies\Psr\Cache;

/**
 * Exception interface for invalid cache arguments.
 *
 * Any time an invalid argument is passed into a method it must throw an
 * exception class which implements ROP\Dependencies\Psr\Cache\InvalidArgumentException.
 */
interface InvalidArgumentException extends CacheException
{
}
