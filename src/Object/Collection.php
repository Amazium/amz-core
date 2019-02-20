<?php

namespace Amz\Core\Object;

use Amz\Core\Contracts\ArrayConstructable;
use Amz\Core\Contracts\CreatableFromArray;
use Amz\Core\Contracts\Extractable;
use Amz\Core\Contracts\Hydratable;
use Amz\Core\Contracts\Named;
use Amz\Core\Object\Exception\InvalidElementTypeException;
use ArrayAccess;
use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Traversable;

abstract class Collection implements Extractable, Hydratable, ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The payload
     *
     * @var array
     */
    private $elements = [];

    /**
     * @var string|null
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
     * @param mixed $element
     * @param mixed $offset
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

    /** @var callable|null */
    private $creator;

    /**
     * @return callable
     */
    public function creator()
    {
        if (!is_callable($this->creator)) {
            $elementClass = $this->elementClass();
            $interfaces = class_implements($elementClass);
            $isNameable = in_array(Named::class, $interfaces, true);
            $fromArrayCallback = [ $elementClass, 'fromArray' ];

            // Check if we can construct the element by providing a payload array in the constructor
            if (in_array(ArrayConstructable::class, $interfaces, true)) {
                $this->creator = function (array $payload, $offset = null) use ($elementClass, $isNameable) {
                    if ($isNameable && !isset($payload['name'])) {
                        $payload['name'] = $offset;
                    }
                    return new $elementClass($payload);
                };
            // Check if we can construct an element by using the static fromArray method
            } elseif (in_array(CreatableFromArray::class, $interfaces, true)
                      && is_callable($fromArrayCallback)
            ) {
                $this->creator = function (
                    array $payload,
                    $offset = null
                ) use (
                    $elementClass,
                    $isNameable,
                    $fromArrayCallback
                ) {
                    if ($isNameable && !isset($payload['name'])) {
                        $payload['name'] = $offset;
                    }
                    return call_user_func($fromArrayCallback, $payload);
                };
            // Check if we can fill the object by using an exchangeArray method
            } elseif (in_array(Hydratable::class, $interfaces, true)) {
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
     * @return void
     */
    public function offsetSet($offset, $element): void
    {
        $this->createAndCheckElement($element, $offset);
        if ($this->useNameAsKey && $element instanceof Named) {
            $offset = $element->name();
        }
        $this->elements[$this->cleanOffset($offset)] = $element;
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
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
        if (!is_null($this->keyStrategy) && trim($this->keyStrategy) !== '') {
            // TODO: perform key naming strategy adjustments
        }
        return $offset;
    }

    /**
     * @return Iterator|ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return $this->iterator;
    }
}
