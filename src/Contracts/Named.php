<?php

namespace Amz\Core\Contracts;

interface Named
{
    /**
     * Get the name of the object
     *
     * @return string
     */
    public function name(): string;
}
