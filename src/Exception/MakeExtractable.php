<?php

namespace Amz\Core\Exception;

trait MakeExtractable
{
    /**
     * @param array $options
     * @return array
     */
    public function getArrayCopy(array $options = []): array
    {
        return [
            'message' => $this->getMessage(),
            'code'    => $this->getCode(),
            'file'    => $this->getFile(),
            'line'    => $this->getLine(),
            'trace'   => $this->getTrace(),
        ];
    }
}
