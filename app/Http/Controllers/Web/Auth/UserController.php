<?php

namespace App\Http\Controllers\Web\Auth;

use App\Concerns\HasDataTable;
use App\Http\Controllers\Web\WebController;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use App\Services\Auth\RoleService;
use App\Services\Auth\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends WebController
{
    use HasDataTable;

    public function __construct(
        private readonly UserService $userService,
        private readonly RoleService $roleService,
    ) {}

    public function index(Request $request): Response
    {
        $users = $this->dataTable(
            query: User::query()->with('roles'),
            request: $request,
            searchable: ['name', 'email', 'username'],
        );

        return Inertia::render('auth/User/Index', [
            'users'   => UserResource::collection($users),
            'roles'   => $this->roleService->all(),
            'filters' => $request->only('search'),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->create($request->validated());

        return redirect()->route('auth.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    public function show(User $user): Response
    {
        return Inertia::render('auth/User/Show', [
            'user'  => $this->userService->find($user->id),
            'roles' => $this->roleService->all(),
        ]);
    }


    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->update($user, $request->validated());

        return redirect()->route('auth.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->userService->delete($user);

        return redirect()->route('auth.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
