<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Events;

use Illuminate\Contracts\Events\Dispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Bridges PSR-14 event dispatch calls into Laravel's built-in event dispatcher.
 * Allows core to fire framework-agnostic events that Laravel listeners can handle.
 */
final class LaravelEventDispatcher implements EventDispatcherInterface
{
    public function __construct(private readonly Dispatcher $dispatcher)
    {
    }

    /**
     * @template T of object
     * @param T $event
     * @return T
     */
    public function dispatch(object $event): object
    {
        $this->dispatcher->dispatch($event);

        return $event;
    }
}
