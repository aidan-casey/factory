<?php

namespace AidanCasey\Factory\Persister;

interface Persister
{
    public function persist(object $object): void;
}