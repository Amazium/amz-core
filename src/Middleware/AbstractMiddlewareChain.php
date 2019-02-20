<?php
namespace Amz\Core\Middleware;

use Amz\Core\IO\Input;
use Amz\Core\IO\Output;
use Amz\Core\IO\Output\ArrayOutput;
use Amz\Core\IO\Context;
use Amz\Core\IO\Context\ArrayContext;

abstract class AbstractMiddlewareChain implements MiddlewareChain
{
    /** @var callable */
    private $chain;

    /**
     * MiddlewareChain constructor.
     * @param Middleware[] $middlewareList
     */
    private function __construct(array $middlewareList)
    {
        $this->chain = $this->createMiddlewareChain($middlewareList);
    }

    /**
     * @param array $middlewareList
     * @return MiddlewareChain
     */
    public static function fromArrayOfMiddleware(array $middlewareList): MiddlewareChain
    {
        foreach ($middlewareList as $key => $middleware) {
            $middlewareList[$key] = self::translateMiddleware($middleware);
        }
        return new static($middlewareList);
    }

    /**
     * @param mixed $middleware
     * @return Middleware
     */
    public static function translateMiddleware($middleware): Middleware
    {
        return $middleware;
    }

    /**
     * @param array $middlewareList
     * @return \Closure
     */
    public function createMiddlewareChain(array $middlewareList)
    {
        $lastCallable = function (): Output {
            return new ArrayOutput();
        };
        while ($middleware = array_pop($middlewareList)) {
            if (!$middleware instanceof Middleware) {
                continue;
            }
            $lastCallable = function (Input $input, Context $context) use ($middleware, $lastCallable) {
                return $middleware($input, $context, $lastCallable);
            };
        }
        return $lastCallable;
    }

    /**
     * @param Input $input
     * @param Context|null $context
     * @return Output
     */
    protected function process(Input $input, Context $context = null): Output
    {
        $chain = $this->chain;
        return $chain(
            $input,
            $context ?? new ArrayContext()
        );
    }
}
