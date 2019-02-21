<?php

namespace Amz\Core\Object;

use Amz\Core\Contracts\Extractable;
use ArrayAccess;
use ArrayObject as SplArrayObject;
use Countable;
use IteratorAggregate;
use Serializable;
use Traversable;

class ArrayObject implements Extractable, IteratorAggregate, ArrayAccess, Serializable, Countable
{
    /** @var SplArrayObject */
    private $arrayObject;

    /**
     * ArrayObject constructor.
     * @param array $input
     * @param int $flags
     * @param string $iterator_class
     */
    public function __construct($input = [], int $flags = 0, string $iterator_class = "ArrayIterator")
    {
        $this->arrayObject = new SplArrayObject($input, $flags, $iterator_class);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getArrayCopy(array $options = []): array
    {
        // Extract extractables
        $arrayCopy = array_map(
            function ($element) use ($options) {
                if ($element instanceof Extractable) {
                    return $element->getArrayCopy($options);
                }
                return $element;
            },
            $this->arrayObject->getArrayCopy()
        );

        // Filter out nulls if not required
        if (boolval($options[ Extractable::EXTOPT_INCLUDE_NULL_VALUES ] ?? false)) {
            $arrayCopy = array_filter(
                $arrayCopy,
                function ($value) {
                    return !is_null($value);
                },
                ARRAY_FILTER_USE_BOTH
            );
        }

        // Return the array copy
        return $arrayCopy;
    }

    /**
     * @return \ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return $this->arrayObject->getIterator();
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->arrayObject->offsetExists($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->arrayObject->offsetGet($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value = null): void
    {
        $this->arrayObject->offsetSet($offset, $value);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        $this->arrayObject->offsetUnset($offset);
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return $this->arrayObject->serialize();
    }

    /**
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized): void
    {
        $this->arrayObject->unserialize($serialized);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->arrayObject->count();
    }
}
