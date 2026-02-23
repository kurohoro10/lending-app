<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'asked_by',
        'asked_at',
        'question',
        'question_type',
        'options',
        'answer',
        'answered_by',
        'answered_at',
        'answer_ip',
        'status',
        'is_mandatory',
        'read_by',
        'read_at',
    ];

    protected $casts = [
        'options'      => 'array',
        'asked_at'     => 'datetime',
        'answered_at'  => 'datetime',
        'is_mandatory' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function askedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asked_by');
    }

    public function answeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    public function readBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    /**
     * Check if this answered question is unread by admins
     */
    public function isUnread(): bool
    {
        return $this->status === 'answered' && $this->read_at === null;
    }

    /**
     * Mark question as read by current admin
     */
    public function markAsRead(int $userId): void
    {
        if ($this->status === 'answered' && !$this->read_at) {
            $this->update([
                'read_by' => $userId,
                'read_at' => now(),
            ]);
        }
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAnswered($query)
    {
        return $query->where('status', 'answered');
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function isAnswered(): bool
    {
        return $this->status === 'answered' && !empty($this->answer);
    }
}
