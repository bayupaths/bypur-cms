<?php

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Menu\StoreMenuGroupRequest;
use App\Http\Requests\Menu\UpdateMenuGroupRequest;
use App\Models\MenuGroup;
use App\Services\Menu\MenuGroupService;
use Illuminate\Http\JsonResponse;

class MenuGroupController extends ApiController
{
    public function __construct(private readonly MenuGroupService $service) {}

    public function index(): JsonResponse
    {
        $groups = $this->service->paginate(
            (int) request('per_page', 15),
            request()->only('search')
        );

        return $this->success($groups);
    }

    public function show(MenuGroup $menuGroup): JsonResponse
    {
        return $this->success($this->service->find($menuGroup->id));
    }

    public function store(StoreMenuGroupRequest $request): JsonResponse
    {
        $group = $this->service->create($request->validated());

        return $this->created($group, 'Menu group created.');
    }

    public function update(UpdateMenuGroupRequest $request, MenuGroup $menuGroup): JsonResponse
    {
        $group = $this->service->update($menuGroup, $request->validated());

        return $this->success($group, 'Menu group updated.');
    }

    public function destroy(MenuGroup $menuGroup): JsonResponse
    {
        $this->service->delete($menuGroup);

        return $this->noContent();
    }
}
