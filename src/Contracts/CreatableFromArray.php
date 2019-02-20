<?php

namespace Amz\Core\Contracts;

interface CreatableFromArray
{
    /**
     * @param array $payload
     * @return mixed
     */
    public static function fromArray(array $payload);
}