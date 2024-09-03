<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionStoreRequest;
use App\Http\Requests\Admin\PermissionUpdateRequest;
use App\Http\Resources\Admin\PermissionResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    public function index(Request $request)
    {
        $itemsPerPage = $request->input('itemsPerPage', 5);
        $search = $request->input('search');
        $sortBy = $request->input('sortBy', 'id');
        $orderBy = $request->input('orderBy', 'asc');

        $permissions = Permission::when($search, fn ($query) => $query->where('name', 'like', "%$search%"));

        $permissions->when($sortBy, fn ($query) => $query->orderBy($sortBy, $orderBy));

        return PermissionResource::collection($permissions->paginate($itemsPerPage));
    }

    public function store(PermissionStoreRequest $request)
    {
        $role = Permission::create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
        ]);

        return new PermissionResource($role);
    }

    public function show(Permission $permission) {}

    public function update(PermissionUpdateRequest $request, Permission $permission)
    {
        $permission->update($request->validated());

        return new PermissionResource($permission);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->noContent(201);
    }
}
