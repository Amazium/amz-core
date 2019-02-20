<?php

namespace Amz\Core\IO\Context;

use Amz\Core\IO\Context;
use Amz\Core\Object\Collection;

class ContextCollection extends Collection implements Context
{
    /**
     * @return string
     */
    public function elementClass(): string
    {
        return Context::class;
    }
}
