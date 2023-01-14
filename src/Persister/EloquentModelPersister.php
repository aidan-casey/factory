<?php

namespace AidanCasey\Factory\Persister;

use RuntimeException;

final class EloquentModelPersister implements Persister
{
    public function persist(object $object): void
    {
        if (! is_subclass_of($object, '\Illuminate\Database\Eloquent\Model')) {
            throw new RuntimeException('Object must be an instance of [Illuminate\Database\Eloquent\Model].');
        }

        $object->save();
    }
}