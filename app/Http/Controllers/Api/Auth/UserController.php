<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Models\User;
use App\Services\Auth\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends ApiController
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(): JsonResponse
    {
        $users = $this->userService->paginate(
            perPage: (int) request('per_page', 15),
            filters: request()->only('search')
        );

        return $this->success($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return $this->created($user->load('roles'));
    }

    public function show(User $user): JsonResponse
    {
        return $this->success($this->userService->find($user->id));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updated = $this->userService->update($user, $request->validated());

        return $this->success($updated);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);

        return $this->noContent();
    }
}
