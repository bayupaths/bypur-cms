<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\ApiController;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;

class ProfileController extends ApiController
{
    public function __construct(private ProfileService $profileService)
    {
    }

    public function show(): JsonResponse
    {
        $profile = $this->profileService->getFirstWithRelations();

        return $this->success($profile);
    }
}
