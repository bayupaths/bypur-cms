<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\ApiController;
use App\Services\Profile\ExperienceService;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;

class ExperienceController extends ApiController
{
    public function __construct(
        private ProfileService $profileService,
        private ExperienceService $experienceService,
    ) {
    }

    public function index(): JsonResponse
    {
        $profile = $this->profileService->getFirst();
        $experiences = $this->experienceService->allByProfile($profile);

        return $this->success($experiences);
    }
}
