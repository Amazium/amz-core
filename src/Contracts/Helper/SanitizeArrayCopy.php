<?php

namespace Amz\Core\Contracts\Helper;

use Amz\Core\Contracts\Extractable;

class SanitizeArrayCopy
{
    public static function sanitize(array $array, array $options)
    {
        // Extract extractables
        array_walk(
            $array,
            function (&$item) use ($options) {
                if ($item instanceof Extractable) {
                    return $item->getArrayCopy($options);
                }
                return $item;
            }
        );

        // Filter on null values?
        $includeNullValues = boolval($options[ Extractable::EXTOPT_INCLUDE_NULL_VALUES ] ?? false);
        if (!$includeNullValues) {
            $array = array_filter(
                $array,
                function ($value, $key) {
                    return !is_null($value);
                },
                ARRAY_FILTER_USE_BOTH
            );
        }
        return $array;
    }
}
