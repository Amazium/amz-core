<?php

namespace Amz\Core\Object;

use Amz\Core\Contracts\ArrayConstructable;
use Amz\Core\Contracts\CreatableFromArray;
use Amz\Core\Contracts\Extractable;
use Amz\Core\Contracts\Hydratable;
use Amz\Core\Contracts\Nameable;
use Amz\Core\Object\Exception\InvalidElementTypeException;
use ArrayAccess;
use Countable;
use Iterator;

abstract class Collection implements Extractable, Hydratable, ArrayAccess, Countable, Iterator
{
    /**
     * The payload
     *
     * @var array
     */
    private $elements = [];

    /**
     * @var string
     */
    private $keyStrategy;

    /**
     * @var Iterator
     */
    private $iterator;

    /**
     * @var bool
     */
    protected $useNameAsKey = true;

    /**
     * The element class used in the collection
     *
     * @return string
     */
    abstract public function elementClass(): string;

    /**
     * Collection constructor.
     * @param array $elements
     * @param string|null $keyStrategy
     */
    public function __construct(
        array $elements = [],
        string $keyStrategy = null
    ) {
        $this->exchangeArray($elements);
        $this->keyStrategy = $keyStrategy;
        $this->iterator = new \ArrayIterator($this->elements);
    }

    /**
     * @param array $elements
     * @return mixed|void
     */
    public function exchangeArray(array $elements)
    {
        foreach ($elements as $offset => $element) {
            $this->offsetSet($offset, $element);
        }
    }

    /**
     * @param $element
     * @param $offset
     * @return mixed
     */
    public function createAndCheckElement(&$element, $offset)
    {
        // If we received an array, try to create an object from type element class of it
        if (is_array($element)) {
            $creator = $this->creator();
            $element = $creator($element, $offset);
        }

        // Check if we have a valid element class instance
        $elementClass = $this->elementClass();
        if (!$element instanceof $elementClass) {
            throw InvalidElementTypeException::fromInvalidElement(static::class, $elementClass, $element);
        }

        // Return the element
        return $element;
    }

    /** @var callable */
    private $creator;

    /**
     * @return callable
     */
    public function creator()
    {
        if (!$this->creator) {
            $elementClass = $this->elementClass();
            $interfaces = class_implements($elementClass);
            $isNameable = in_array(Nameable::class, $interfaces);
            if (in_array(ArrayConstructable::class, $interfaces)) {
                $this->creator = function (array $payload, $offset = null) use ($elementClass, $isNameable) {
                    if ($isNameable && !isset($payload['name'])) {
                        $payload['name'] = $offset;
                    }
                    return new $elementClass($payload);
                };
            } elseif (in_array(CreatableFromArray::class, $interfaces)) {
                $this->creator = function (array $payload, $offset = null) use ($elementClass, $isNameable) {
                    if ($isNameable && !isset($payload['name'])) {
                        $payload['name'] = $offset;
                    }
                    return call_user_func([$elementClass, 'fromArray'], $payload);
                };
            } elseif (in_array(Hydratable::class, $interfaces)) {
                $this->creator = function (array $payload, $offset = null) use ($elementClass, $isNameable) {
                    if ($isNameable && !isset($payload['name'])) {
                        $payload['name'] = $offset;
                    }
                    /** @var Hydratable $element */
                    $element = new $elementClass;
                    $element->exchangeArray($payload);
                    return $element;
                };
            } else {
                $this->creator = function ($element) {
                    return $element;
                };
            }
        }
        return $this->creator;
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
            $this->elements
        );

        // Filter out nulls if not required
        if (empty($options[ Extractable::EXTOPT_INCLUDE_NULL_VALUES ])) {
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
     * @return int
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        $offset = $this->cleanOffset($offset);
        return array_key_exists($offset, $this->elements);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $offset = $this->cleanOffset($offset);
        if (!$this->offsetExists($offset)) {
            return null;
        }
        return $this->elements[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $element
     */
    public function offsetSet($offset, $element)
    {
        $this->createAndCheckElement($element, $offset);
        if ($this->useNameAsKey && $element instanceof Nameable) {
            $offset = $element->name();
        }
        $this->elements[$this->cleanOffset($offset)] = $element;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $offset = $this->cleanOffset($offset);
        unset($this->elements[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function cleanOffset($offset)
    {
        if (is_int($offset)) {
            return $offset;
        }
        if ($this->keyStrategy) {
            // TODO: perform key naming strategy adjustments
        }
        return $offset;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->iterator->current();
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }

}
