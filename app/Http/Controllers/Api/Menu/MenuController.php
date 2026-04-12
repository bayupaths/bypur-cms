<?php

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Menu\ReorderMenuRequest;
use App\Http\Requests\Menu\StoreMenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Models\Menu;
use App\Services\Menu\MenuService;
use Illuminate\Http\JsonResponse;

class MenuController extends ApiController
{
    public function __construct(private readonly MenuService $service) {}

    public function index(): JsonResponse
    {
        $menus = $this->service->paginate(
            (int) request('per_page', 15),
            request()->only('search', 'group_id', 'is_active')
        );

        return $this->success($menus);
    }

    public function show(Menu $menu): JsonResponse
    {
        return $this->success($this->service->find($menu->id));
    }

    public function store(StoreMenuRequest $request): JsonResponse
    {
        $menu = $this->service->create($request->validated());

        return $this->created($menu, 'Menu item created.');
    }

    public function update(UpdateMenuRequest $request, Menu $menu): JsonResponse
    {
        $menu = $this->service->update($menu, $request->validated());

        return $this->success($menu, 'Menu item updated.');
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $this->service->delete($menu);

        return $this->noContent();
    }

    public function reorder(ReorderMenuRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('items'));

        return $this->success(null, 'Menu order saved.');
    }

    /** Ambil menu tree untuk group tertentu berdasarkan akses user yang login. */
    public function tree(string $group): JsonResponse
    {
        $menus = $this->service->treeForUser($group, request()->user());

        return $this->success($menus);
    }
}
