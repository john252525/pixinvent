<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleStoreRequest;
use App\Http\Requests\Admin\RoleUpdateRequest;
use App\Http\Resources\Admin\RoleResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function index(Request $request)
    {
        $itemsPerPage = $request->input('itemsPerPage', 5);
        $search = $request->input('search');
        $permissionId = $request->input('permissionId');
        $sortBy = $request->input('sortBy', 'id');
        $orderBy = $request->input('orderBy', 'asc');

        $roles = Role::with('permissions')
            ->when($search, fn ($query) => $query
                ->where('name', 'like', "%$search%"));

        $roles->when($permissionId, fn ($query) => $query->whereRelation('roles', 'id', $permissionId));

        $roles->when($sortBy, fn ($query) => $query->orderBy($sortBy, $orderBy));

        return RoleResource::collection($roles->paginate($itemsPerPage))->additional([
            'permissions' => Permission::select('id', 'name')->distinct()->get()
                ->map(fn ($permission) => ['title' => $permission->name, 'value' => $permission->id])
                ->toArray(),
        ]);
    }

    public function store(RoleStoreRequest $request)
    {
        $role = Role::create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($this->extractPermissions($request->validated('permissions')));

        return new RoleResource($role);
    }

    public function show(Role $role) {}

    public function update(RoleUpdateRequest $request, Role $role)
    {
        $role->update(['name' => $request->validated('name')]);
        $role->syncPermissions($this->extractPermissions($request->validated('permissions')));

        return new RoleResource($role);
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->noContent(201);
    }

    private function extractPermissions($roles)
    {
        return Permission::whereIn('id', \Arr::pluck($roles, 'value'))->get();
    }
}
