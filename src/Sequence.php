<?php

namespace AidanCasey\Factory;

final class Sequence
{
    private readonly array $values;

    private int $currentIndex = 0;

    private int $maxIndex = 0;

    public function __construct(array $values)
    {
        $this->values = $values;
        $this->maxIndex = count($this->values) - 1;
    }

    public function __invoke(): mixed
    {
        $value = $this->values[$this->currentIndex];

        $this->currentIndex = ($this->maxIndex === $this->currentIndex) ? 0 : $this->currentIndex + 1;

        return $value;
    }
}