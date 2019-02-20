<?php

namespace AmzTest\Core\Support\Util;

use Amz\Core\Support\Util\Arr;
use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{
    public function baseExportProviderMultiLine(): array
    {
        $ex1 = <<<TST
[
    'a' => 1,
    'b' => 2,
]
TST;
        $ex2 = <<<TST
[
    'a' => 'bla di blah "\' ok',
    'b' => [
        'c' => 1,
    ],
]
TST;
        return [
            [
                [
                    "a" => 1,
                    "b" => 2,
                ],
                $ex1
            ],
            [
                [
                    "a" => "bla di blah \"' ok",
                    "b" => [
                        "c" => 1,
                    ],
                ],
                $ex2
            ]
        ];
    }

    /**
     * @param mixed $input
     * @param string $output
     * @dataProvider baseExportProviderMultiLine
     */
    public function testExportMultiLine(array $input, string $output)
    {
        $actual = Arr::export($input);
        $this->assertEquals($output, $actual);
    }

    public function baseExportProviderSingleLine(): array
    {
        $ex1 = "[ 'a' => 1, 'b' => 2 ]";
        $ex2 = "[ 'a' => 'bla di blah \"\\' ok', 'b' => [ 'c' => 1 ] ]";
        $ex3 = "[ 'a', 'b' ]";
        return [
            [
                [
                    "a" => 1,
                    "b" => 2,
                ],
                $ex1
            ],
            [
                [
                    "a" => "bla di blah \"' ok",
                    "b" => [
                        "c" => 1,
                    ],
                ],
                $ex2
            ],
            [
                [ 'a', 'b' ],
                $ex3,
            ]
        ];
    }

    /**
     * @param array $input
     * @param string $output
     * @dataProvider baseExportProviderSingleLine
     */
    public function testExportSingleLine(array $input, string $output)
    {
        $actual = Arr::export($input, true);
        $this->assertEquals($output, $actual);
    }
}
