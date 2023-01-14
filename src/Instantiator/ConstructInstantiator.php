<?php

namespace AidanCasey\Factory\Instantiator;

final class ConstructInstantiator implements Instantiator
{
    public function instantiate(string $class, array $attributes): object
    {
        return new $class(...$attributes);
    }
}