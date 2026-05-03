<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Tests\Studio;

use InfilePhp\Laravel\Tests\TestCase;
use InfilePhp\Laravel\Studio\StudioServiceProvider;
use InfilePhp\Laravel\FelServiceProvider;

final class BuilderApiTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FelServiceProvider::class,
        ];
    }

    public function test_it_generates_xml_preview(): void
    {
        $response = $this->postJson('/fel-studio/api/builder/preview', [
            'recipient' => [
                'tax_id' => '12345678',
                'name' => 'ACME Corp',
                'address' => 'Guatemala',
            ],
            'items' => [
                [
                    'type' => 'B',
                    'description' => 'Test Item',
                    'quantity' => 2,
                    'unit_price' => 50.00,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'xml',
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertStringContainsString('<dte:GTDocumento', $response->json('xml'));
    }

    public function test_it_validates_dte_structure(): void
    {
        // Missing items should fail
        $response = $this->postJson('/fel-studio/api/builder/validate', [
            'recipient' => [
                'tax_id' => 'CF',
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('An invoice requires at least one line item', $response->json('error'));

        // Valid payload
        $response = $this->postJson('/fel-studio/api/builder/validate', [
            'recipient' => [
                'tax_id' => 'CF',
            ],
            'items' => [
                [
                    'type' => 'S',
                    'description' => 'Service',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}
