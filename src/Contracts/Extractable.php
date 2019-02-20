<?php

namespace Amz\Core\Contracts;

interface Extractable
{
    /**
     * Default behaviour can exclude null values from exporting
     */
    const EXTOPT_INCLUDE_NULL_VALUES = 'INCLUDE_NULL_VALUES';

    /**
     * Get an array representation of the object
     *
     * @param array $options
     * @return array
     */
    public function getArrayCopy(array $options = []): array;
}
