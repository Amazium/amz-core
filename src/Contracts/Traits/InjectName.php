<?php

namespace Amz\Core\Contracts\Traits;

trait InjectName
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    protected function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
}
