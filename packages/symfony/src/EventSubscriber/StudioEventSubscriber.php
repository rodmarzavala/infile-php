<?php

declare(strict_types=1);

namespace InfilePhp\Symfony\EventSubscriber;

use InfilePhp\Core\Dte\Events\DteCancelled;
use InfilePhp\Core\Dte\Events\DteFailed;
use InfilePhp\Core\Dte\Events\DteIssued;
use InfilePhp\Core\Dte\Events\FallbackActivated;
use InfilePhp\Symfony\Studio\Storage\StudioRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class StudioEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private StudioRepository $repository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DteIssued::class => 'onDteIssued',
            DteFailed::class => 'onDteFailed',
            FallbackActivated::class => 'onFallbackActivated',
            DteCancelled::class => 'onDteCancelled',
        ];
    }

    public function onDteIssued(DteIssued $event): void
    {
        $payload = $event->payload;
        $this->repository->logTransaction([
            'uuid' => $payload->uuid,
            'serie' => $payload->serie,
            'numero' => $payload->numero,
            'dte_type' => $payload->type->value,
            'recipient_tax_id' => $payload->recipientTaxId,
            'idempotency_key' => $payload->idempotencyKey,
            'status' => 'issued',
            'payload' => $payload->rawResponse,
            'error_message' => null,
        ]);
    }

    public function onDteFailed(DteFailed $event): void
    {
        $payload = $event->payload;
        $this->repository->logTransaction([
            'dte_type' => $payload->type->value,
            'recipient_tax_id' => $payload->recipientTaxId,
            'idempotency_key' => $payload->idempotencyKey,
            'status' => 'failed',
            'payload' => $payload->rawResponse,
            'error_message' => $payload->errorMessage,
        ]);
    }

    public function onFallbackActivated(FallbackActivated $event): void
    {
        $payload = $event->payload;
        $this->repository->logTransaction([
            'dte_type' => $payload->type->value,
            'recipient_tax_id' => $payload->recipientTaxId,
            'idempotency_key' => $payload->idempotencyKey,
            'status' => 'pending',
            'payload' => null,
            'error_message' => 'Contingencia CAFE activada. Se encoló para reintento.',
        ]);
    }

    public function onDteCancelled(DteCancelled $event): void
    {
        $payload = $event->payload;
        $this->repository->logTransaction([
            'uuid' => $payload->uuid,
            'status' => 'cancelled',
            'payload' => $payload->rawResponse,
            'error_message' => null,
        ]);
    }
}
