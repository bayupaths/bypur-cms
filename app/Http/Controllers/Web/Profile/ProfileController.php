<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Web\WebController;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\User;
use App\Services\Profile\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends WebController
{
    public function __construct(
        private readonly ProfileService $profileService,
    ) {}

    public function show(): Response
    {
        $user    = Auth::user();
        $profile = $this->profileService->findOrCreate($user);

        return Inertia::render('Profile/Profile/Show', [
            'profile'  => $profile,
            'authUser' => $user,
        ]);
    }

    public function showUser(User $user): Response
    {
        $profile = $this->profileService->findByUser($user->id);

        return Inertia::render('Profile/Profile/Show', [
            'profile'  => $profile,
            'authUser' => $user,
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->profileService->updateProfile(Auth::user(), $validated);

        return redirect()->route('profile.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
