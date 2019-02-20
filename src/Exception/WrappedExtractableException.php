<?php

namespace Amz\Core\Exception;

use Amz\Core\Contracts\Extractable;
use Throwable;

class WrappedExtractableException extends Exception implements ExtractableException
{
    /**
     * WrappedExtractableException constructor.
     * @param Throwable|null $previous
     */
    public function __construct(Throwable $previous = null)
    {
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
    }

    /**
     * @param Throwable $previous
     * @return ExtractableException
     */
    public static function fromException(Throwable $previous): ExtractableException
    {
        if ($previous instanceof ExtractableException) {
            return $previous;
        }
        return new static($previous);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getArrayCopy(array $options = []): array
    {
        // We treat $previous as our current exception
        // Basic export logic
        $export = [
            'message' => $this->getPrevious()->getMessage(),
            'code'    => $this->getPrevious()->getCode(),
            'file'    => $this->getPrevious()->getFile(),
            'line'    => $this->getPrevious()->getLine(),
            'trace'   => $this->getPrevious()->getTrace(),
        ];

        // Previous exception if included
        $previous = $this->getPrevious()->getPrevious();
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
