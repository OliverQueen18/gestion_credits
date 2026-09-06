<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationCorrection extends Model
{
    protected $fillable = [
        'operation_id',
        'operation_correction_id',
        'user_id',
        'motif',
    ];

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function operationCorrection(): BelongsTo
    {
        return $this->belongsTo(Operation::class, 'operation_correction_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
