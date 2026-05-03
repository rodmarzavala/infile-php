<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Tests\Studio;

use InfilePhp\Laravel\Tests\TestCase;
use InfilePhp\Laravel\Studio\Storage\StudioRepository;
use Illuminate\Support\Facades\File;

class StudioRepositoryTest extends TestCase
{
    private string $dbPath;
    private StudioRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->dbPath = storage_path('app/fel-studio.sqlite');
        
        // Ensure clean state
        if (File::exists($this->dbPath)) {
            File::delete($this->dbPath);
        }

        $this->repository = new StudioRepository();
    }

    protected function tearDown(): void
    {
        if (File::exists($this->dbPath)) {
            File::delete($this->dbPath);
        }
        
        parent::tearDown();
    }


    public function test_it_logs_transaction_and_retrieves_timeline(): void
    {
        $this->repository->logTransaction([
            'uuid' => '1234-abcd',
            'serie' => 'A',
            'numero' => '1',
            'dte_type' => 'FACT',
            'recipient_tax_id' => '12345678',
            'idempotency_key' => 'idemp-123',
            'status' => 'issued',
            'payload' => ['some' => 'data']
        ]);

        $timeline = $this->repository->getTimeline();

        $this->assertCount(1, $timeline);
        $this->assertEquals('1234-abcd', $timeline[0]['uuid']);
        $this->assertEquals('FACT', $timeline[0]['dte_type']);
        $this->assertEquals('issued', $timeline[0]['status']);
        $this->assertEquals(['some' => 'data'], $timeline[0]['payload']);
    }

    public function test_it_clears_the_timeline(): void
    {
        $this->repository->logTransaction([
            'uuid' => '1234-abcd',
        ]);

        $this->assertCount(1, $this->repository->getTimeline());

        $this->repository->clear();

        $this->assertCount(0, $this->repository->getTimeline());
    }
}
