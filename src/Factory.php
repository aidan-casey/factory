<?php

namespace AidanCasey\Factory;

use AidanCasey\Factory\Instantiator\ConstructInstantiator;
use AidanCasey\Factory\Instantiator\Instantiator;
use AidanCasey\Factory\Persister\ObjectPersister;
use AidanCasey\Factory\Persister\Persister;
use Closure;
use Faker\Factory as FakerFactory;
use Faker\Generator;

/**
 * @template TObject of object
 */
abstract class Factory
{
    private Instantiator $instantiator;

    private Persister $persister;

    /**
     * @var array<callable>
     */
    private array $beforeInstantiating = [];

    /**
     * @var array<callable>
     */
    private array $afterInstantiating = [];

    /**
     * @var array<callable>
     */
    private array $beforePersisting = [];

    /**
     * @var array<callable>
     */
    private array $afterPersisting = [];

    /**
     * @var array<callable>
     */
    private array $states = [];

    protected Generator $faker;

    abstract protected function getClass(): string;

    abstract protected function getDefinition(): array;

    public static function new(): static
    {
        return new static;
    }

    /**
     * @return TObject
     */
    public static function createOne(array $attributes = []): object
    {
        return static::new()->create($attributes);
    }

    /**
     * @return TObject
     */
    public static function makeOne(array $attributes = []): object
    {
        return static::new()->make($attributes);
    }

    final public function __construct()
    {
        $this->instantiateWith(new ConstructInstantiator);
        $this->persistWith(new ObjectPersister);

        $this->configure();

        $this->faker = FakerFactory::create();
    }

    /**
     * @return TObject
     */
    public function create(array $attributes = []): object
    {
        $object = $this->make($attributes);

        $this->persisting($object);

        $this->persister->persist($object);

        $this->persisted($object);

        return $object;
    }

    /**
     * @return TObject
     */
    public function make(array $attributes = []): object
    {
        $this->state($attributes);

        $attributes = [];

        foreach ($this->states as $state) {
            $attributes = array_replace_recursive($attributes, $state($attributes));
        }

        foreach ($this->getDefinition() as $attribute => $default) {
            if (isset($attributes[$attribute])) {
                continue;
            }

            if (is_callable($default)) {
                $default = $default($attributes);
            }

            $attributes[$attribute] = $default;
        }

        $this->instantiating($attributes);

        $object = $this->instantiator->instantiate($this->getClass(), $attributes);

        $this->instantiated($object, $attributes);

        return $object;
    }

    /**
     * @return array<TObject>
     */
    public function createCount(int $count): array
    {
        $created = [];
        $currentCount = 0;

        while ($currentCount < $count) {
            $created[] = $this->create();

            $currentCount++;
        }

        return $created;
    }

    /**
     * @return array<TObject>
     */
    public function makeCount(int $count): array
    {
        $made = [];
        $currentCount = 0;

        while ($currentCount < $count) {
            $made[] = $this->make();

            $currentCount++;
        }

        return $made;
    }

    public function sequence(...$sequence): static
    {
        return $this->state(
            new Sequence($sequence)
        );
    }

    protected function beforeInstantiating(callable $callable): static
    {
        $this->beforeInstantiating[] = $callable;

        return $this;
    }

    protected function afterInstantiating(callable $callable): static
    {
        $this->afterInstantiating[] = $callable;

        return $this;
    }

    protected function beforePersisting(callable $callable): static
    {
        $this->beforePersisting[] = $callable;

        return $this;
    }

    protected function afterPersisting(callable $callable): static
    {
        $this->afterPersisting[] = $callable;

        return $this;
    }

    protected function instantiating(array $attributes): void
    {
        foreach ($this->beforeInstantiating as $instantiating) {
            $instantiating($attributes);
        }
    }

    protected function instantiated(object $object, array $attributes): void
    {
        foreach ($this->afterInstantiating as $instantiated) {
            $instantiated($object, $attributes);
        }
    }

    protected function persisting(object $object): void
    {
        foreach ($this->beforePersisting as $persisting) {
            $persisting($object);
        }
    }

    protected function persisted(object $object): void
    {
        foreach ($this->afterPersisting as $persisted) {
            $persisted($object);
        }
    }

    protected function configure(): void
    {
        //
    }

    protected function instantiateWith(Instantiator $instantiator): static
    {
        $this->instantiator = $instantiator;

        return $this;
    }

    protected function persistWith(Persister $persister): static
    {
        $this->persister = $persister;

        return $this;
    }

    protected function state(callable|array $state): static
    {
        if (! is_callable($state)) {
            $state = fn() => $state;
        }

        $this->states[] = $state;

        return $this;
    }
}