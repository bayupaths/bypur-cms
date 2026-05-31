<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Web\WebController;
use App\Http\Requests\Profile\StoreEducationRequest;
use App\Http\Requests\Profile\UpdateEducationRequest;
use App\Models\Education;
use App\Services\Profile\EducationService;
use App\Services\Profile\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EducationController extends WebController
{
    public function __construct(
        private readonly EducationService $educationService,
        private readonly ProfileService $profileService,
    ) {}

    public function index(): Response
    {
        $profile    = $this->profileService->findByUser(Auth::id());
        $educations = $profile ? $this->educationService->allByProfile($profile) : collect();

        return Inertia::render('Profile/Education/Index', [
            'educations' => $educations,
            'hasProfile' => $profile !== null,
        ]);
    }

    public function store(StoreEducationRequest $request): RedirectResponse
    {
        $profile = $this->profileService->findByUser(Auth::id());

        if (! $profile) {
            return redirect()->route('profile.show')
                ->with('error', 'Lengkapi profil terlebih dahulu.');
        }

        $this->educationService->create($profile, $request->validated());

        return redirect()->route('profile.educations.index')
            ->with('success', 'Pendidikan berhasil ditambahkan.');
    }

    public function update(UpdateEducationRequest $request, Education $education): RedirectResponse
    {
        $this->educationService->update($education, $request->validated());

        return redirect()->route('profile.educations.index')
            ->with('success', 'Pendidikan berhasil diperbarui.');
    }

    public function destroy(Education $education): RedirectResponse
    {
        $this->educationService->delete($education);

        return redirect()->route('profile.educations.index')
            ->with('success', 'Pendidikan berhasil dihapus.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $this->educationService->reorder($request->ids);

        return redirect()->route('profile.educations.index');
    }
}
