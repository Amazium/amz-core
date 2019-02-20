<?php

namespace Amz\Core\Contracts;

interface Nameable
{
    /**
     * Get the name of the object
     *
     * @return string
     */
    public function name(): string;
}