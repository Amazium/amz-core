<?php

namespace Amz;

use Amz\Core\Contracts\ArrayConstructable;
use Amz\Core\Contracts\Extractable;
use Amz\Core\Contracts\Identifiable;
use Amz\Core\Contracts\Nameable;
use Amz\Core\Contracts\Traits\InjectName;
use Amz\Core\Contracts\Traits\InjectTextualIdentifier;
use Amz\Core\Object\Collection;

require_once '../vendor/autoload.php';

class X1Coll extends Collection
{
    public function elementClass(): string
    {
        return X1::class;
    }
}

class X1 implements ArrayConstructable, Identifiable, Nameable, Extractable
{
    use InjectTextualIdentifier, InjectName;

    public function __construct(array $payload)
    {
        $this->setId($payload['id']);
        $this->setName($payload['name']);
    }

    public function getArrayCopy(array $options = []): array
    {
        return [
            'id' => $this->id(),
            'name' => $this->getName(),
        ];
    }
}

$coll = new X1Coll([
    'xxsdfx' => [ 'id' => 'a1' ],
    'asdas' => [ 'id' => 'a2' ],
    'sadadas' => [ 'id' => 'a3' ],
]);
print_r($coll->getArrayCopy());

foreach ($coll as $key => $val) {
    echo "KEY: $key\n" . print_r($val->getArrayCopy(), true) . PHP_EOL;
}