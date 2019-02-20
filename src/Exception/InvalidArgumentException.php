<?php

namespace Amz\Core\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

/**
 * Exception thrown if an argument does not match with the expected value.
 * @link https://php.net/manual/en/class.invalidargumentexception.php
 */
class InvalidArgumentException extends BaseInvalidArgumentException implements ExtractableException
{
    use MakeExtractable;
}
