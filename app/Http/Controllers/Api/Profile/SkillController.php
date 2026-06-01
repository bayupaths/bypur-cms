<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\ApiController;
use App\Services\Profile\SkillService;
use Illuminate\Http\JsonResponse;

class SkillController extends ApiController
{
    public function __construct(private SkillService $skillService)
    {
    }

    public function index(): JsonResponse
    {
        return $this->success($this->skillService->all());
    }
}
