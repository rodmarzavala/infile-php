<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Studio\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfilePhp\Core\Dte\Invoice;
use InfilePhp\Core\Dte\Item;
use InfilePhp\Core\Dte\Recipient;
use InfilePhp\Core\InfilePhp;

final class BuilderController
{
    public function preview(Request $request): JsonResponse
    {
        try {
            $dte = $this->buildDteFromRequest($request);
            $xml = InfilePhp::client()->getUnsignedXml($dte);

            return new JsonResponse([
                'success' => true,
                'xml' => $xml,
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function validate(Request $request): JsonResponse
    {
        try {
            $dte = $this->buildDteFromRequest($request);

            // Core structure validation (throws if missing recipient/items)
            $dte->validate();

            // TODO: Once XSD validation is fully implemented in core, it should be called here
            $xml = InfilePhp::client()->getUnsignedXml($dte);

            return new JsonResponse([
                'success' => true,
                'message' => 'DTE structure is valid.',
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    private function buildDteFromRequest(Request $request): Invoice
    {
        // For now, Studio builder only supports FACT (Invoice)
        $invoice = Invoice::create();

        $recipientData = $request->input('recipient', []);

        if (($recipientData['tax_id'] ?? '') === 'CF') {
            $invoice->forFinalConsumer();
        } elseif (!empty($recipientData)) {
            $invoice->for(
                Recipient::withTaxId($recipientData['tax_id'] ?? '')
                    ->name($recipientData['name'] ?? 'Ciudadano')
                    ->address($recipientData['address'] ?? 'Ciudad')
            );
        }

        $items = $request->input('items', []);
        foreach ($items as $itemData) {
            $type = $itemData['type'] ?? 'B'; // Bien by default
            $description = $itemData['description'] ?? 'Item';
            $quantity = (float) ($itemData['quantity'] ?? 1);
            $unitPrice = (float) ($itemData['unit_price'] ?? 0.0);

            if ($type === 'S') {
                $item = Item::service($description);
            } else {
                $item = Item::product($description);
            }

            $item->quantity($quantity)->unitPrice($unitPrice);
            $invoice->add($item);
        }

        return $invoice;
    }
}
