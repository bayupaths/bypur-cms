<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\StoreRoleRequest;
use App\Http\Requests\Auth\SyncPermissionsRequest;
use App\Http\Requests\Auth\UpdateRoleRequest;
use App\Models\Role;
use App\Services\Auth\RoleService;
use Illuminate\Http\JsonResponse;

class RoleController extends ApiController
{
    public function __construct(
        private readonly RoleService $roleService,
    ) {}

    public function index(): JsonResponse
    {
        $roles = $this->roleService->paginate(
            perPage: (int) request('per_page', 15),
            filters: request()->only('search', 'guard')
        );

        return $this->success($roles);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create($request->validated());

        return $this->created($role->load('permissions'));
    }

    public function show(Role $role): JsonResponse
    {
        return $this->success($this->roleService->find($role->id));
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $updated = $this->roleService->update($role, $request->validated());

        return $this->success($updated);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->roleService->delete($role);

        return $this->noContent();
    }

    public function syncPermissions(SyncPermissionsRequest $request, Role $role): JsonResponse
    {
        $updated = $this->roleService->syncPermissions($role, $request->validated('permissions'));

        return $this->success($updated->load('permissions'));
    }
}
