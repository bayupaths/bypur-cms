<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Web\WebController;
use App\Http\Requests\Profile\StoreCertificateRequest;
use App\Http\Requests\Profile\UpdateCertificateRequest;
use App\Models\Certificate;
use App\Services\Profile\CertificateService;
use App\Services\Profile\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends WebController
{
    public function __construct(
        private readonly CertificateService $certificateService,
        private readonly ProfileService $profileService,
    ) {}

    public function index(): Response
    {
        $profile      = $this->profileService->findByUser(Auth::id());
        $certificates = $profile ? $this->certificateService->allByProfile($profile) : collect();

        return Inertia::render('Profile/Certificate/Index', [
            'certificates' => $certificates,
            'hasProfile'   => $profile !== null,
        ]);
    }

    public function store(StoreCertificateRequest $request): RedirectResponse
    {
        $profile = $this->profileService->findByUser(Auth::id());

        if (! $profile) {
            return redirect()->route('profile.show')
                ->with('error', 'Lengkapi profil terlebih dahulu.');
        }

        $this->certificateService->create($profile, $request->validated());

        return redirect()->route('profile.certificates.index')
            ->with('success', 'Sertifikat berhasil ditambahkan.');
    }

    public function update(UpdateCertificateRequest $request, Certificate $certificate): RedirectResponse
    {
        $this->certificateService->update($certificate, $request->validated());

        return redirect()->route('profile.certificates.index')
            ->with('success', 'Sertifikat berhasil diperbarui.');
    }

    public function destroy(Certificate $certificate): RedirectResponse
    {
        $this->certificateService->delete($certificate);

        return redirect()->route('profile.certificates.index')
            ->with('success', 'Sertifikat berhasil dihapus.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $this->certificateService->reorder($request->ids);

        return redirect()->route('profile.certificates.index');
    }
}
