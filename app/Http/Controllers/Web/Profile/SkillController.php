<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Web\WebController;
use App\Http\Requests\Profile\StoreSkillRequest;
use App\Http\Requests\Profile\UpdateSkillRequest;
use App\Models\Skill;
use App\Services\Profile\SkillService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends WebController
{
    public function __construct(
        private readonly SkillService $skillService,
    ) {}

    public function index(Request $request): Response
    {
        $skills = $this->skillService->paginate(20, $request->only('search', 'category'));

        return Inertia::render('Profile/Skill/Index', [
            'skills'  => $skills,
            'filters' => $request->only('search', 'category'),
        ]);
    }

    public function store(StoreSkillRequest $request): RedirectResponse
    {
        $this->skillService->create($request->validated());

        return redirect()->route('profile.skills.index')
            ->with('success', 'Skill berhasil ditambahkan.');
    }

    public function update(UpdateSkillRequest $request, Skill $skill): RedirectResponse
    {
        $this->skillService->update($skill, $request->validated());

        return redirect()->route('profile.skills.index')
            ->with('success', 'Skill berhasil diperbarui.');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $this->skillService->delete($skill);

        return redirect()->route('profile.skills.index')
            ->with('success', 'Skill berhasil dihapus.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $this->skillService->reorder($request->ids);

        return redirect()->route('profile.skills.index');
    }
}
