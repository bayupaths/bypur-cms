<?php

namespace App\Http\Controllers\Web\Menu;

use App\Concerns\HasDataTable;
use App\Http\Controllers\Web\WebController;
use App\Http\Requests\Menu\ReorderMenuRequest;
use App\Http\Requests\Menu\StoreMenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Http\Resources\Menu\MenuResource;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Menu\MenuGroupService;
use App\Services\Menu\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends WebController
{
    use HasDataTable;

    public function __construct(
        private readonly MenuService $service,
        private readonly MenuGroupService $groupService,
    ) {}

    public function index(Request $request): Response
    {
        $menus = $this->dataTable(
            query: Menu::query()->with(['group', 'parent', 'roles', 'permissions']),
            request: $request,
            searchable: ['title', 'slug', 'url'],
        );

        return Inertia::render('Menu/Index', [
            'menus'        => MenuResource::collection($menus),
            'groups'       => $this->groupService->all(),
            'parent_menus' => Menu::active()->ordered()->get(['id', 'title', 'group_id']),
            'roles'        => Role::orderBy('name')->get(['id', 'name', 'display_name']),
            'permissions'  => Permission::orderBy('name')->get(['id', 'name', 'display_name']),
        ]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('menu.index')
            ->with('success', 'Menu item created.');
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $this->service->update($menu, $request->validated());

        return redirect()->route('menu.index')
            ->with('success', 'Menu item updated.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->service->delete($menu);

        return redirect()->route('menu.index')
            ->with('success', 'Menu item deleted.');
    }

    public function reorder(ReorderMenuRequest $request): RedirectResponse
    {
        $this->service->reorder($request->validated('items'));

        return back()->with('success', 'Menu order saved.');
    }
}
