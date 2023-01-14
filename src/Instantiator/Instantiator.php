<?php

namespace AidanCasey\Factory\Instantiator;

interface Instantiator
{
    public function instantiate(string $class, array $attributes): object;
}