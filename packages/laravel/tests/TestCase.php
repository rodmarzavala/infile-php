<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:laE1U2iF/Lw2qT7N3jP4c/M/o/P/r/L/b/L/V/S/l/Q=');
    }
}
