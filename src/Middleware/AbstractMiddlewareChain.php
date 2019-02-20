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
     * @param Middleware ...$middleware
     */
    private function __construct(Middleware ...$middleware)
    {
        $this->chain = $this->createMiddlewareChain($middleware);
    }

    /**
     * @param $middleware
     * @return AbstractMiddlewareChain
     */
    public static function fromMiddleware(Middleware ...$middleware): MiddlewareChain
    {
        return self::fromArrayOfMiddleware($middleware);
    }

    /**
     * @param array $middleware
     * @return AbstractMiddlewareChain
     */
    public static function fromArrayOfMiddleware(array $middleware): MiddlewareChain
    {
        foreach ($middleware as $key => $mw) {
            $middleware[$key] = self::translateMiddleware($mw);
        }
        return new static($middleware);
    }

    /**
     * @param $middleware
     * @return Middleware
     */
    public static function translateMiddleware($middleware): Middleware
    {
        return $middleware;
    }

    /**
     * @param Middleware ...$middlewareList
     * @return \Closure
     */
    public function createMiddlewareChain(Middleware ...$middlewareList)
    {
        $lastCallable = function (): Output {
            return new ArrayOutput();
        };
        while ($middleware = array_pop($middlewareList)) {
            $lastCallable = function (Input $input, Context $context) use ($middleware, $lastCallable) {
                return $middleware($input, $context, $lastCallable);
            };
        }
        return $lastCallable;
    }

    /**
     * @param Input $message
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
