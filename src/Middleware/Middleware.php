<?php
namespace Amz\Core\Middleware;

use Amz\Core\IO\Context;
use Amz\Core\IO\Input;
use Amz\Core\IO\Output;

interface Middleware
{
    public function __invoke(Input $input, Context $output, callable $next): Output;
}
