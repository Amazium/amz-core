<?php

namespace Amz\Core\Contracts\Traits;

trait InjectTextualIdentifier
{
    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id
     */
    protected function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }
}
