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
use Traversable;

abstract class Collection implements Extractable, Hydratable, ArrayAccess, Countable, Iterator
{
    /**
     * The payload
     *
     * @var array
     */
    private $elements = [];

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var array
     */
    private $keyMapping = [
    ];

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
    public function __construct(array $elements = [])
    {
        $this->exchangeArray($elements);
    }

    /**
     * @param array $elements
     * @return mixed|void
     */
    public function exchangeArray(array $elements)
    {
        $this->elements = [];
        foreach ($elements as $offset => $element) {
            $this->offsetSet($offset, $element);
        }
    }

    /**
     * @param mixed $element
     * @param mixed $offset
     * @return mixed
     */
    public function createAndCheckElement(&$element, $offset = null)
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
                    $fromArrayCallback,
                    $isNameable
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
        $isNamed = null;
        $elements = [];
        foreach ($this->elements as $element) {
            if (is_null($isNamed)) {
                $isNamed = $element instanceof Named;
            }
            if ($isNamed) {
                $elements[$element->name()] = $element;
            } else {
                $elements[] = $element;
            }
        }

        // Extract extractables
        array_walk(
            $elements,
            function (&$item) use ($options) {
                return $item instanceof Extractable ? $item->getArrayCopy($options) : $item;
            }
        );

        array_walk(
            $elements,
            function (&$element) use ($options) {
                if ($element instanceof Extractable) {
                    return $element->getArrayCopy($options);
                }
                return $element;
            },
        );

        // Filter on null values?
        $includeNullValues = boolval($options[ Extractable::EXTOPT_INCLUDE_NULL_VALUES ] ?? false);
        if (!$includeNullValues) {
            $elements = array_filter(
                $elements,
                function ($value, $key) {
                    return !is_null($value);
                },
                ARRAY_FILTER_USE_BOTH
            );
        }

        // Return the array copy
        return $elements;
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
        return isset($this->elements[$offset]) || isset($this->keyMapping[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (isset($this->elements[$offset])) {
            return $this->elements[$offset];
        } elseif (isset($this->keyMapping[$offset])) {
            return $this->elements[ $this->keyMapping[$offset] ];
        } else {
            return null;
        }
    }

    /**
     * @param mixed $offset
     * @param mixed $element
     * @return void
     */
    public function offsetSet($offset, $element): void
    {
        $index = null;
        $this->createAndCheckElement($element, $offset);
        if (is_numeric($offset)) {
            $index = $offset;
        }
        if (is_numeric($offset) && $element instanceof Named) {
            $offset = $element->name();
        }
        if (!is_numeric($offset)) {
            $currentIndex = isset($this->keyMapping[$offset]) ? $this->keyMapping[$offset] : null;
            if (!is_null($currentIndex)) {
                $index = $currentIndex;
            }
            $this->keyMapping[$offset] = $index;
        }
        $this->elements[$index] = $element;
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        // First we do numeric offsets
        if (is_numeric($offset) && isset($this->elements[$offset])) {
            unset($this->elements[$offset]);
            if (in_array($offset, $this->keyMapping)) {
                $mapping = array_flip($this->keyMapping);
                unset($this->keyMapping[ $mapping[$offset] ]);
            }
            return;
        }

        // Then unset by key
        if (!is_numeric($offset) && isset($this->keyMapping[$offset])) {
            $index = $this->keyMapping[$offset];
            unset($this->elements[$index]);
            unset($this->keyMapping[$offset]);
        }
    }

    /**
     * @param mixed $element
     */
    public function append($element)
    {
        $this->offsetSet(count($this->elements), $element);
    }

    /**
     * @param mixed $element
     */
    public function prepend($element)
    {
        array_unshift($this->elements, null);
        foreach ($this->keyMapping as $key => $index) {
            $this->keyMapping[$key] = $index + 1;
        }
        $this->offsetSet(0, $element);
    }

    /**
     * @param Collection $collection
     */
    public function merge(Collection $collection)
    {
        foreach ($collection as $offset => $element) {
            if (is_numeric($offset)) {
                $this->append($element);
            } else {
                $this->offsetSet($offset, $element);
            }
        }
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        return $this->elements[$this->index];
    }

    /**
     * Add 1 to the pointer
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * Return the current key value
     *
     * @return int|string
     */
    public function key()
    {
        if ($this->elements[$this->index] instanceof Named) {
            return $this->elements[$this->index]->name();
        }
        $this->index;
    }

    /**
     * Is the internal pointer in a valid location
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->elements[$this->index]);
    }

    /**
     * Reset the pointer
     */
    public function rewind(): void
    {
        $this->index = 0;
    }
}
