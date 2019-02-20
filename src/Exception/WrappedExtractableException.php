<?php

namespace Amz\Core\Exception;

use Amz\Core\Contracts\Extractable;
use Throwable;

class WrappedExtractableException implements ExtractableException
{
    /** @var Throwable */
    private $exception;

    /**
     * WrappedExtractableException constructor.
     * @param Throwable $exception
     */
    private function __construct(Throwable $exception)
    {
        $this->exception = $exception;
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

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->exception->getMessage();
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->exception->getCode();
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->exception->getFile();
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->exception->getLine();
    }

    /**
     * @return array
     */
    public function getTrace()
    {
        return $this->exception->getTrace();
    }

    /**
     * @return string
     */
    public function getTraceAsString()
    {
        return $this->exception->getTraceAsString();
    }

    /**
     * @return Throwable
     */
    public function getPrevious()
    {
        return $this->exception->getPrevious();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->exception->__toString();
    }

}
