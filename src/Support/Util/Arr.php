<?php

namespace Amz\Core\Support\Util;

class Arr
{
    public static function export(
        array $expression,
        bool $asOneLine = false,
        int $indentSize = 4,
        bool $indentFirstLine = false
    ): ?string {
        $export = var_export($expression, true);
        $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
        $array = preg_split("/\r\n|\n|\r/", (string)$export) ?: [];
        $array = preg_replace(
            [ "/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/" ],
            [ null, ']$1', ' => [' ],
            $array
        ) ?: [];
        array_unshift($array, "[");
        $export = $indentFirstLine ? str_repeat(' ', $indentSize) : '';
        $export.= join(
            $asOneLine ? ' ' : PHP_EOL . ($indentSize > 0 ? str_repeat(' ', $indentSize) : ''),
            array_filter($array)
        );
        if ($asOneLine) {
            $export = preg_replace(
                [
                    '/[0-9*] => /',
                    '/\[\s*\'/',
                    '/,\s*([0-9a-zA-Z\[\'])/',
                    '/\[\s*([0-9a-zA-Z\[\'])/',
                    '/\',\s*\'/',
                    '/([0-9]),\s*\'/',
                    '/\',\s*\]/',
                    '/([0-9]),\s*\]/',
                    '/\],\s*\]/',
                ],
                [
                    '',
                    '[ \'',
                    ', $1',
                    '[ $1',
                    '\', \'',
                    '$1, \'',
                    '\' ]',
                    '$1 ]',
                    '] ]'
                ],
                $export
            );
        } else {
            $export = preg_replace(
                [
                    '/[0-9*] => /',
                ],
                [
                    '',
                ],
                $export
            );
        }
        return $export;
    }
}
