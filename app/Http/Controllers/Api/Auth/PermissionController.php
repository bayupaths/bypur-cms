<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\StorePermissionRequest;
use App\Http\Requests\Auth\UpdatePermissionRequest;
use App\Models\Permission;
use App\Services\Auth\PermissionService;
use Illuminate\Http\JsonResponse;

class PermissionController extends ApiController
{
    public function __construct(
        private readonly PermissionService $permissionService,
    ) {}

    public function index(): JsonResponse
    {
        $permissions = $this->permissionService->paginate(
            perPage: (int) request('per_page', 15),
            filters: request()->only('search', 'group', 'guard')
        );

        return $this->success($permissions);
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->permissionService->create($request->validated());

        return $this->created($permission->load('conditions'));
    }

    public function show(Permission $permission): JsonResponse
    {
        return $this->success($this->permissionService->find($permission->id));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $updated = $this->permissionService->update($permission, $request->validated());

        return $this->success($updated);
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $this->permissionService->delete($permission);

        return $this->noContent();
    }
}
