<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserCreateRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Http\Resources\Admin\UsersResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $itemsPerPage = $request->input('itemsPerPage', 5);
        $search = $request->input('search');
        $roleId = $request->input('roleId');
        $sortBy = $request->input('sortBy', 'id');
        $orderBy = $request->input('orderBy', 'asc');

        $users = User::with('roles')
            ->when($search, fn ($user) => $user
                ->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%"));
        $users->when($roleId, fn ($user) => $user->whereRelation('roles', 'id', $roleId));
        $users->when($sortBy, fn ($user) => $user->orderBy($sortBy, $orderBy));

        return UsersResource::collection($users->paginate($itemsPerPage))->additional([
            'roles' => Role::select('id', 'name')->distinct()->get()
                ->map(fn ($role) => ['title' => $role->name, 'value' => $role->id])
                ->toArray(),
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $relation = $request->input('relation');

        $users = User::when($relation, fn ($user) => $user->has($relation));
        $users->when($search, fn ($user) => $user
            ->where('name', 'like', "%$search%")
            ->orWhere('email', 'like', "%$search%"));

        return UsersResource::collection($users->paginate(10));
    }

    public function store(UserCreateRequest $request)
    {
        $requestRoles = $request->validated('roles');
        $requestPassword = $request->validated('password');
        $requestUser = \Arr::except($request->validated(), ['roles', 'password']);
        $requestUser['password'] = \Hash::make($requestPassword);
        $user = User::create($requestUser);

        $user->syncRoles($this->extractRoles($requestRoles));

        return new UsersResource($user);
    }

    public function show(User $user)
    {
        return new UsersResource($user);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $requestRoles = $request->validated('roles');
        $requestPassword = $request->validated('password');
        $requestUser = \Arr::except($request->validated(), ['roles', 'password']);
        if ($requestPassword) {
            $requestUser['password'] = \Hash::make($requestPassword);
        }

        $user->update($requestUser);

        $user->syncRoles($this->extractRoles($requestRoles));

        return new UsersResource($user);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->noContent(201);
    }

    private function extractRoles($roles)
    {
        return Role::whereIn('id', \Arr::pluck($roles, 'value'))->get();
    }
}
