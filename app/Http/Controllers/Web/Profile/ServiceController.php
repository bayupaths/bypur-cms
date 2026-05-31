<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Web\WebController;
use App\Http\Requests\Profile\StoreServiceRequest;
use App\Http\Requests\Profile\UpdateServiceRequest;
use App\Models\Service;
use App\Services\Profile\ServiceService;
use App\Services\Profile\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends WebController
{
    public function __construct(
        private readonly ServiceService $serviceService,
        private readonly ProfileService $profileService,
    ) {}

    public function index(): Response
    {
        $profile  = $this->profileService->findByUser(Auth::id());
        $services = $profile ? $this->serviceService->allByProfile($profile) : collect();

        return Inertia::render('Profile/Service/Index', [
            'services'   => $services,
            'hasProfile' => $profile !== null,
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $profile = $this->profileService->findByUser(Auth::id());

        if (! $profile) {
            return redirect()->route('profile.show')
                ->with('error', 'Lengkapi profil terlebih dahulu.');
        }

        $this->serviceService->create($profile, $request->validated());

        return redirect()->route('profile.services.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $this->serviceService->update($service, $request->validated());

        return redirect()->route('profile.services.index')
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->serviceService->delete($service);

        return redirect()->route('profile.services.index')
            ->with('success', 'Layanan berhasil dihapus.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $this->serviceService->reorder($request->ids);

        return redirect()->route('profile.services.index');
    }

    public function toggle(Service $service): RedirectResponse
    {
        $this->serviceService->toggleActive($service);

        return redirect()->route('profile.services.index')
            ->with('success', 'Status layanan berhasil diubah.');
    }
}
