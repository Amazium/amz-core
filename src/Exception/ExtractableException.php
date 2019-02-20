<?php

namespace Amz\Core\Exception;

use Amz\Core\Contracts\Extractable;
use Throwable;

interface ExtractableException extends Throwable, Extractable
{

}