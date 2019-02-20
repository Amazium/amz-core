<?php

namespace Amz\Core\Decorator;

use Amz\Core\Contracts\Decoratable;
use Amz\Core\Exception\BadMethodCallException;

class AbstractDecorator implements Decorator
{
    /** @var Decoratable */
    private $decorated;

    /**
     * AbstractDecorator constructor.
     * @param Decoratable $decorated
     */
    public function __construct(Decoratable $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @return Decoratable
     */
    public function decorated(): Decoratable
    {
        return $this->decorated;
    }

    /**
     * @return Decoratable
     */
    public function root(): Decoratable
    {
        if ($this->decorated instanceof Decorator) {
            return $this->decorated->root();
        }
        return $this->decorated;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $callback = [ $this->decorated, $name ];
        if (!method_exists($this->decorated, $name) || !is_callable($callback)) {
            throw new BadMethodCallException(sprintf(
                'No method %s on class %s found',
                $name,
                static::class
            ));
        }
        return call_user_func_array($callback, $arguments);
    }
}
