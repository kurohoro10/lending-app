<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'provider',
        'reference_number',
        'request_data',
        'response_data',
        'credit_score',
        'status',
        'notes',
        'requested_by',
        'requested_at',
        'completed_at',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'credit_score' => 'integer',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function markAsCompleted(array $responseData, ?int $creditScore = null): void
    {
        $this->update([
            'status' => 'completed',
            'response_data' => $responseData,
            'credit_score' => $creditScore,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $errorMessage,
            'completed_at' => now(),
        ]);
    }
}
