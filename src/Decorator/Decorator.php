<?php

namespace Amz\Core\Decorator;

use Amz\Core\Contracts\Decoratable;

interface Decorator extends Decoratable
{
    /**
     * @return Decoratable
     */
    public function decorated(): Decoratable;

    /**
     * @return Decoratable
     */
    public function root(): Decoratable;
}
