<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Tests\Studio;

use InfilePhp\Laravel\Tests\TestCase;
use InfilePhp\Laravel\Studio\StudioServiceProvider;
use InfilePhp\Laravel\Studio\Storage\StudioRepository;
use Illuminate\Support\Facades\File;

class TimelineApiTest extends TestCase
{
    private string $dbPath;

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            StudioServiceProvider::class,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->dbPath = storage_path('app/fel-studio.sqlite');
        if (File::exists($this->dbPath)) {
            File::delete($this->dbPath);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->dbPath)) {
            File::delete($this->dbPath);
        }
        
        parent::tearDown();
    }

    public function test_it_returns_empty_timeline_initially(): void
    {
        $response = $this->get('/fel-studio/api/timeline');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => []
        ]);
    }

    public function test_it_returns_logged_transactions(): void
    {
        $repository = $this->app->make(StudioRepository::class);
        $repository->logTransaction([
            'uuid' => 'test-uuid',
            'status' => 'issued'
        ]);

        $response = $this->get('/fel-studio/api/timeline');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(1, $data);
        $this->assertEquals('test-uuid', $data[0]['uuid']);
        $this->assertEquals('issued', $data[0]['status']);
    }

    public function test_it_serves_the_frontend_catchall_route(): void
    {
        // We simulate the presence of the index.html file
        $indexPath = public_path('vendor/fel-studio');
        if (!File::exists($indexPath)) {
            File::makeDirectory($indexPath, 0755, true);
        }
        File::put($indexPath . '/index.html', '<html>FEL Studio</html>');

        $response = $this->get('/fel-studio/anything');

        $response->assertStatus(200);
        $response->assertSee('FEL Studio');

        // Clean up
        File::deleteDirectory($indexPath);
    }
}
