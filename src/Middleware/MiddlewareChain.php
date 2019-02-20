<?php
namespace Amz\Core\Middleware;

interface MiddlewareChain
{
    /**
     * @param Middleware[] $middleware
     * @return MiddlewareChain
     */
    public static function fromMiddleware(array $middleware): MiddlewareChain;
}
