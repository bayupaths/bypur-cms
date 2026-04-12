<?php

namespace App\Http\Controllers\Web\Auth;

use App\Concerns\HasDataTable;
use App\Http\Controllers\Web\WebController;
use App\Http\Requests\Auth\StorePermissionRequest;
use App\Http\Requests\Auth\UpdatePermissionRequest;
use App\Http\Resources\Auth\PermissionResource;
use App\Models\Permission;
use App\Services\Auth\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PermissionController extends WebController
{
    use HasDataTable;

    public function __construct(
        protected PermissionService $permissionService,
    ) {}

    public function index(Request $request): Response
    {
        $permissions = $this->dataTable(Permission::query(), $request, searchable: [
            'name',
            'display_name',
            'group',
            'description',
        ]);

        return Inertia::render('auth/Permission/Index', [
            'permissions' => PermissionResource::collection($permissions),
            'groups'      => $this->permissionService->groups(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Auth/Permission/Create', [
            'groups' => $this->permissionService->groups(),
        ]);
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $guardName = $data['guard_name'] ?? 'web';

            if ($data['mode'] === 'module_crud') {
                foreach ($data['selected_actions'] as $action) {
                    $this->permissionService->create([
                        'name'       => $data['group'] . '.' . $action,
                        'group'      => $data['group'],
                        'guard_name' => $guardName,
                    ]);
                }
            } else {
                $this->permissionService->create([
                    'name'         => $data['group'] . '.' . $data['action'],
                    'display_name' => $data['display_name'] ?? null,
                    'description'  => $data['description'] ?? null,
                    'group'        => $data['group'],
                    'guard_name'   => $guardName,
                ]);
            }

            return redirect()->route('auth.permissions.index')
                ->with('success', 'Permission berhasil dibuat.');
        } catch (Throwable $e) {
            Log::error('Failed to create permission', ['error' => $e->getMessage()]);

            return redirect()->back()->withInput()
                ->with('error', 'Gagal membuat permission.');
        }
    }

    public function show(Permission $permission): Response
    {
        return Inertia::render('Auth/Permission/Show', [
            'permission' => $this->permissionService->find($permission->id),
        ]);
    }

    public function edit(Permission $permission): Response
    {
        return Inertia::render('Auth/Permission/Edit', [
            'permission' => $this->permissionService->find($permission->id),
            'groups'     => $this->permissionService->groups(),
        ]);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        try {
            $this->permissionService->update($permission, $request->validated());

            return redirect()->route('auth.permissions.index')
                ->with('success', 'Permission berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('Failed to update permission', ['id' => $permission->id, 'error' => $e->getMessage()]);

            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui permission.');
        }
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        try {
            $this->permissionService->delete($permission);

            return redirect()->route('auth.permissions.index')
                ->with('success', 'Permission berhasil dihapus.');
        } catch (Throwable $e) {
            Log::error('Failed to delete permission', ['id' => $permission->id, 'error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus permission. Pastikan permission tidak sedang digunakan.');
        }
    }
}
