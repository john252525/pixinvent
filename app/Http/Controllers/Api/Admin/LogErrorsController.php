<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\LogErrorsResource;
use App\Models\LogErrors;
use Illuminate\Http\Request;

class LogErrorsController extends Controller
{
    public function index(Request $request)
    {
        $itemsPerPage = $request->input('itemsPerPage', 5);
        $search = $request->input('search');
        $sortBy = $request->input('sortBy', 'id');
        $orderBy = $request->input('orderBy', 'desc');

        $logs = LogErrors::with('user')->when($search, fn ($query) => $query->whereJsonContains('errors', 'like', "%$search%"));

        $logs->when($sortBy, fn ($query) => $query->orderBy($sortBy, $orderBy));

        return LogErrorsResource::collection($logs->paginate($itemsPerPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'exists:users'],
            'type' => ['nullable'],
            'errors' => ['required'],
        ]);

        return new LogErrorsResource(LogErrors::create($data));
    }

    public function show(LogErrors $logErrors)
    {
        return new LogErrorsResource($logErrors);
    }

    public function update(Request $request, LogErrors $logErrors)
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'exists:users'],
            'type' => ['nullable'],
            'errors' => ['required'],
        ]);

        $logErrors->update($data);

        return new LogErrorsResource($logErrors);
    }

    public function destroy(LogErrors $logErrors)
    {
        $logErrors->delete();

        return response()->json(['success' => true, 'message' => __('Log deleted successfully')]);
    }
}
