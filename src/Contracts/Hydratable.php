<?php

namespace Amz\Core\Contracts;

interface Hydratable
{
    /**
     * Apply a payload to the object
     *
     * @param array $payload
     * @return mixed
     */
    public function exchangeArray(array $payload);
}
