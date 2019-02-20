<?php
namespace Amz\Core\Middleware;

interface MiddlewareChain
{
    /**
     * @param Middleware ...$middleware
     * @return MiddlewareChain
     */
    public static function fromMiddleware(Middleware ...$middleware): MiddlewareChain;

    /**
     * @param array $middleware
     * @return MiddlewareChain
     */
    public static function fromArrayOfMiddleware(array $middleware): MiddlewareChain;
}
