<?php

namespace AidanCasey\Factory\Instantiator;

final class EloquentModelInstantiator implements Instantiator
{
    public function instantiate(string $class, array $attributes): object
    {
        return new $class($attributes);
    }
}