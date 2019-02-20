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

    public static function before(string $subject, string $search): string
    {
        return IllStr::before($subject, $search);
    }

    public static function after(string $subject, string $search): string
    {
        return IllStr::after($subject, $search);
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        return IllStr::endsWith($haystack, $needles);
    }
}
