<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\ApiController;
use App\Services\Profile\ProfileService;
use App\Services\Profile\ServiceService;
use Illuminate\Http\JsonResponse;

class ServiceController extends ApiController
{
    public function __construct(
        private ProfileService $profileService,
        private ServiceService $serviceService,
    ) {
    }

    public function index(): JsonResponse
    {
        $profile = $this->profileService->getFirst();
        $services = $this->serviceService->allByProfile($profile);

        return $this->success($services);
    }
}
