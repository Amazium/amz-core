<?php

namespace Amz\Core\Support\Util;

class Arr
{
    public static function export(array $expression, bool $asOneLine = false): string
    {
        $export = var_export($expression, true);
        $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
        $array = preg_split("/\r\n|\n|\r/", $export);
        $array = preg_replace(
            [ "/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/" ],
            [ null, ']$1', ' => [' ],
            $array
        );
        $export = join(
            $asOneLine ? ' ' : PHP_EOL,
            array_filter(["["] + $array)
        );
        if ($asOneLine) {
            var_export($expression);
            var_dump($export);
            $export = preg_replace(
                [
                    '/[0-9*] => /',
                    '/\[\s*\'/',
                    '/\',\s*\'/',
                    '/([0-9]),\s*\'/',
                    '/\',\s*\]/',
                    '/([0-9]),\s*\]/',
                    '/\],\s*\]/',
                ],
                [
                    '',
                    '[ \'',
                    '\', \'',
                    '$1, \'',
                    '\' ]',
                    '$1 ]',
                    '] ]'
                ],
                $export
            );
            var_dump($export);
        }
        return $export;
    }
}
