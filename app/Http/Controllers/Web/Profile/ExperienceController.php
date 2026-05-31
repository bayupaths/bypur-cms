<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Web\WebController;
use App\Http\Requests\Profile\StoreExperienceRequest;
use App\Http\Requests\Profile\UpdateExperienceRequest;
use App\Models\Experience;
use App\Services\Profile\ExperienceService;
use App\Services\Profile\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ExperienceController extends WebController
{
    public function __construct(
        private readonly ExperienceService $experienceService,
        private readonly ProfileService $profileService,
    ) {}

    public function index(): Response
    {
        $profile     = $this->profileService->findByUser(Auth::id());
        $experiences = $profile ? $this->experienceService->allByProfile($profile) : collect();

        return Inertia::render('Profile/Experience/Index', [
            'experiences' => $experiences,
            'hasProfile'  => $profile !== null,
        ]);
    }

    public function store(StoreExperienceRequest $request): RedirectResponse
    {
        $profile = $this->profileService->findByUser(Auth::id());

        if (! $profile) {
            return redirect()->route('profile.show')
                ->with('error', 'Lengkapi profil terlebih dahulu.');
        }

        $this->experienceService->create($profile, $request->validated());

        return redirect()->route('profile.experiences.index')
            ->with('success', 'Pengalaman kerja berhasil ditambahkan.');
    }

    public function update(UpdateExperienceRequest $request, Experience $experience): RedirectResponse
    {
        $this->experienceService->update($experience, $request->validated());

        return redirect()->route('profile.experiences.index')
            ->with('success', 'Pengalaman kerja berhasil diperbarui.');
    }

    public function destroy(Experience $experience): RedirectResponse
    {
        $this->experienceService->delete($experience);

        return redirect()->route('profile.experiences.index')
            ->with('success', 'Pengalaman kerja berhasil dihapus.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $this->experienceService->reorder($request->ids);

        return redirect()->route('profile.experiences.index');
    }
}
