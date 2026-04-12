<?php

namespace App\Http\Controllers\Web\Auth;

use App\Concerns\HasDataTable;
use App\Http\Controllers\Web\WebController;
use App\Http\Requests\Auth\StoreRoleRequest;
use App\Http\Requests\Auth\UpdateRoleRequest;
use App\Http\Resources\Auth\RoleResource;
use App\Models\Role;
use App\Services\Auth\PermissionService;
use App\Services\Auth\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class RoleController extends WebController
{
    use HasDataTable;

    public function __construct(
        protected RoleService $roleService,
        protected PermissionService $permissionService,
    ) {}

    public function index(Request $request): Response
    {
        $roles = $this->dataTable(
            Role::query()->with('permissions')->withCount('users'),
            $request,
            searchable: ['name', 'display_name', 'description'],
        );

        return Inertia::render('auth/Role/Index', [
            'roles'               => RoleResource::collection($roles),
            'permissions_grouped' => $this->permissionService->allGrouped(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        try {
            $this->roleService->create($request->validated());

            return redirect()->route('auth.roles.index')
                ->with('success', 'Role berhasil dibuat.');
        } catch (Throwable $e) {
            Log::error('Failed to create role', ['error' => $e->getMessage()]);

            return redirect()->back()->withInput()
                ->with('error', 'Gagal membuat role.');
        }
    }

    public function show(Role $role): Response
    {
        return Inertia::render('auth/Role/Show', [
            'role' => $this->roleService->find($role->id),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        try {
            $this->roleService->update($role, $request->validated());

            return redirect()->route('auth.roles.index')
                ->with('success', 'Role berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('Failed to update role', ['id' => $role->id, 'error' => $e->getMessage()]);

            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui role.');
        }
    }

    public function destroy(Role $role): RedirectResponse
    {
        try {
            $this->roleService->delete($role);

            return redirect()->route('auth.roles.index')
                ->with('success', 'Role berhasil dihapus.');
        } catch (Throwable $e) {
            Log::error('Failed to delete role', ['id' => $role->id, 'error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus role. Pastikan role tidak sedang digunakan.');
        }
    }
}
