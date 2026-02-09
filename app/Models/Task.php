<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'assigned_to',
        'created_by',
        'task_type',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'completed_at',
        'completed_by',
        'completion_notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function isOverdue(): bool
    {
        return $this->due_date &&
               $this->due_date->isPast() &&
               !in_array($this->status, ['completed', 'cancelled']);
    }

    public function complete(?string $notes = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => auth()->id(),
            'completion_notes' => $notes,
        ]);
    }
}
