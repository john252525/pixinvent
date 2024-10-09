<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogErrors extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'errors',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'errors' => 'array',
        ];
    }
}
