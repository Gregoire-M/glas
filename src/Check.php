<?php

namespace Glas;

class Check
{
    /** @var string */
    private $application;

    /** @var bool */
    private $isUp;

    public function __construct(string $application)
    {
        $this->application = $application;
    }

    public function getApplication(): string
    {
        return $this->application;
    }

    public function isUp(): bool
    {
        return $this->isUp;
    }

    public function setIsUp(bool $isUp)
    {
        $this->isUp = $isUp;
        return $this;
    }
}
