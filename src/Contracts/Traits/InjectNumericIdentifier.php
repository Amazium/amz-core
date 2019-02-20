<?php

namespace Amz\Core\Contracts\Traits;

trait InjectNumericIdentifier
{
    /**
     * @var int
     */
    private $id;

    /**
     * @param int $id
     */
    protected function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }
}
