<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Studio\Listeners;

use Illuminate\Events\Dispatcher;
use InfilePhp\Core\Events\DteCancelled;
use InfilePhp\Core\Events\DteFailed;
use InfilePhp\Core\Events\DteIssued;
use InfilePhp\Laravel\Studio\Storage\StudioRepository;

final readonly class StudioEventSubscriber
{
    public function __construct(
        private StudioRepository $repository
    ) {
    }

    public function handleDteIssued(DteIssued $event): void
    {
        $this->repository->logTransaction([
            'uuid' => $event->uuid,
            'serie' => $event->serie,
            'numero' => $event->numero,
            'dte_type' => $event->dteType->value,
            'recipient_tax_id' => $event->recipientTaxId,
            'idempotency_key' => $event->idempotencyKey,
            'status' => 'issued',
            'payload' => [
                'event' => 'DteIssued',
            ],
        ]);
    }

    public function handleDteFailed(DteFailed $event): void
    {
        $this->repository->logTransaction([
            'uuid' => null,
            'serie' => null,
            'numero' => null,
            'dte_type' => null,
            'recipient_tax_id' => null,
            'idempotency_key' => $event->idempotencyKey,
            'status' => 'failed',
            'error_message' => $event->exception->getMessage(),
            'payload' => [
                'event' => 'DteFailed',
                'exception_class' => get_class($event->exception),
            ],
        ]);
    }

    public function handleDteCancelled(DteCancelled $event): void
    {
        $this->repository->logTransaction([
            'uuid' => $event->uuid,
            'serie' => null,
            'numero' => null,
            'dte_type' => null,
            'recipient_tax_id' => null,
            'idempotency_key' => null,
            'status' => 'cancelled',
            'payload' => [
                'event' => 'DteCancelled',
                'reason' => $event->reason,
            ],
        ]);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            DteIssued::class,
            [self::class, 'handleDteIssued']
        );

        $events->listen(
            DteFailed::class,
            [self::class, 'handleDteFailed']
        );

        $events->listen(
            DteCancelled::class,
            [self::class, 'handleDteCancelled']
        );
    }
}
