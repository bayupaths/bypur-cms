<?php

namespace App\Http\Controllers\Web\Menu;

use App\Concerns\HasDataTable;
use App\Http\Controllers\Web\WebController;
use App\Http\Requests\Menu\StoreMenuGroupRequest;
use App\Http\Requests\Menu\UpdateMenuGroupRequest;
use App\Http\Resources\Menu\MenuGroupResource;
use App\Models\MenuGroup;
use App\Services\Menu\MenuGroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuGroupController extends WebController
{
    use HasDataTable;

    public function __construct(private readonly MenuGroupService $service) {}

    public function index(Request $request): Response
    {
        $groups = $this->dataTable(
            query: MenuGroup::query()->withCount('menus'),
            request: $request,
            searchable: ['name', 'display_name'],
        );

        return Inertia::render('Menu/Group/Index', [
            'groups' => MenuGroupResource::collection($groups),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('menu.groups.index');
    }

    public function store(StoreMenuGroupRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('menu.groups.index')
            ->with('success', 'Menu group created.');
    }

    public function edit(MenuGroup $menuGroup): RedirectResponse
    {
        return redirect()->route('menu.groups.index');
    }

    public function update(UpdateMenuGroupRequest $request, MenuGroup $menuGroup): RedirectResponse
    {
        $this->service->update($menuGroup, $request->validated());

        return redirect()->route('menu.groups.index')
            ->with('success', 'Menu group updated.');
    }

    public function destroy(MenuGroup $menuGroup): RedirectResponse
    {
        $this->service->delete($menuGroup);

        return redirect()->route('menu.groups.index')
            ->with('success', 'Menu group deleted.');
    }
}
