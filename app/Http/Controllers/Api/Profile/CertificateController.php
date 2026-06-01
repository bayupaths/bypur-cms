<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\ApiController;
use App\Services\Profile\CertificateService;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;

class CertificateController extends ApiController
{
    public function __construct(
        private ProfileService $profileService,
        private CertificateService $certificateService,
    ) {
    }

    public function index(): JsonResponse
    {
        $profile = $this->profileService->getFirst();
        $certificates = $this->certificateService->allByProfile($profile);

        return $this->success($certificates);
    }
}
