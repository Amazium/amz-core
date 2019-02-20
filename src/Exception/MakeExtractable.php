<?php

namespace Amz\Core\Exception;

use Amz\Core\Contracts\Extractable;

trait MakeExtractable
{
    /**
     * @param array $options
     * @return array
     */
    public function getArrayCopy(array $options = []): array
    {
        // Basic export logic
        $export = [
            'message' => $this->getMessage(),
            'code'    => $this->getCode(),
            'file'    => $this->getFile(),
            'line'    => $this->getLine(),
            'trace'   => $this->getTrace(),
        ];

        // Previous exception if included
        $previous = $this->getPrevious();
        if (!empty($previous) || !empty($options[ Extractable::EXTOPT_INCLUDE_NULL_VALUES ])) {
            if ($previous instanceof Extractable) {
                $previous = $previous->getArrayCopy($options);
            }
            $export['previous'] = $previous;
        }

        // Return export
        return $export;
    }
}