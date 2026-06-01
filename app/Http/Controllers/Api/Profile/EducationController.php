<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\ApiController;
use App\Services\Profile\EducationService;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;

class EducationController extends ApiController
{
    public function __construct(
        private ProfileService $profileService,
        private EducationService $educationService,
    ) {
    }

    public function index(): JsonResponse
    {
        $profile = $this->profileService->getFirst();
        $educations = $this->educationService->allByProfile($profile);

        return $this->success($educations);
    }
}
