<?php

namespace Amz\Core\Support\Util;

use Illuminate\Support\Str as IllStr;

class Str
{
    public static function camel($text): string
    {
        return IllStr::camel($text);
    }

    public static function snake($text): string
    {
        return IllStr::snake($text);
    }

    public static function kebab($text): string
    {
        return IllStr::kebab($text);
    }

    public static function studly($text): string
    {
        return IllStr::studly($text);
    }
}
