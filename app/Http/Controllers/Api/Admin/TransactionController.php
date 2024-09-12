<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        return TransactionResource::collection(Transaction::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users'],
            'service' => ['required'],
            'status' => ['required'],
            'amount' => ['required', 'integer'],
            'income_amount' => ['nullable', 'integer'],
            'payment_method' => ['nullable'],
            'refunded_amount' => ['nullable'],
        ]);

        return new TransactionResource(Transaction::create($data));
    }

    public function show(Transaction $transaction)
    {
        return new TransactionResource($transaction);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users'],
            'service' => ['required'],
            'status' => ['required'],
            'amount' => ['required', 'integer'],
            'income_amount' => ['nullable', 'integer'],
            'payment_method' => ['nullable'],
            'refunded_amount' => ['nullable'],
        ]);

        $transaction->update($data);

        return new TransactionResource($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return response()->json();
    }
}
