<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Studio\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

final class StudioController extends Controller
{
    public function index(): \Illuminate\Http\Response
    {
        $indexPath = public_path('vendor/fel-studio/index.html');

        if (!File::exists($indexPath)) {
            return response('FEL Studio assets not found. Run `php artisan vendor:publish --tag=fel-studio-assets`', 404);
        }

        $html = File::get($indexPath);

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
