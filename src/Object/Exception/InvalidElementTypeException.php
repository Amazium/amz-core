<?php

namespace Amz\Core\Object\Exception;

use Amz\Core\Exception\InvalidArgumentException;
use Throwable;

class InvalidElementTypeException extends InvalidArgumentException
{
    /**
     * @param string $collectionClass
     * @param string $expectedElementClass
     * @param mixed $actualElement
     * @param int $code
     * @param Throwable|null $previous
     * @return InvalidElementTypeException
     */
    public static function fromInvalidElement(
        string $collectionClass,
        string $expectedElementClass,
        $actualElement,
        int $code = 0,
        Throwable $previous = null
    ): InvalidElementTypeException {
        $message = sprintf(
            'Collection %s expects elements of %s type, but received a %s',
            $collectionClass,
            $expectedElementClass,
            is_object($actualElement) ? get_class($actualElement) : gettype($actualElement)
        );
        return new static($message, $code, $previous);
    }
}
