<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Studio\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use InfilePhp\Laravel\Studio\Storage\StudioRepository;

final class TimelineController extends Controller
{
    public function __construct(
        private readonly StudioRepository $repository
    ) {
    }

    public function index(): JsonResponse
    {
        $timeline = $this->repository->getTimeline();
        
        return response()->json([
            'data' => $timeline
        ]);
    }
}
