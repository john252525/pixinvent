<?php

namespace App\Http\Resources\Admin;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Transaction */
class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service' => $this->service,
            'status' => $this->status,
            'amount' => $this->amount,
            'income_amount' => $this->income_amount,
            'payment_method' => $this->payment_method,
            'refunded_amount' => $this->refunded_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'user_id' => $this->user_id,

            'user' => new UsersResource($this->whenLoaded('user')),
        ];
    }
}
