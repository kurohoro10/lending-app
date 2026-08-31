<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    /**
     * review_status values for assessment-checklist questions. Null means
     * "not part of the checklist" (freeform question) — everything below
     * only ever applies when review_status is non-null.
     */
    public const REVIEW_PENDING         = 'pending';
    public const REVIEW_AWAITING_REVIEW = 'awaiting_review';
    public const REVIEW_APPROVED        = 'approved';
    public const REVIEW_RETURNED        = 'returned';

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
        'doc_category_hint',
        'read_by',
        'read_at',
        'review_status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'options'      => 'array',
        'asked_at'     => 'datetime',
        'answered_at'  => 'datetime',
        'is_mandatory' => 'boolean',
        'read_at'      => 'datetime',
        'reviewed_at'  => 'datetime',
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

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Documents uploaded against this specific question. Replaces the
     * category+timestamp heuristic previously used in question-item.blade.php.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
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

    // =========================================================================
    // Assessment checklist / review pipeline
    // =========================================================================

    public function scopeChecklistItem($query)
    {
        return $query->whereNotNull('review_status');
    }

    public function scopeAwaitingReview($query)
    {
        return $query->where('review_status', self::REVIEW_AWAITING_REVIEW);
    }

    public function scopeReviewApproved($query)
    {
        return $query->where('review_status', self::REVIEW_APPROVED);
    }

    public function scopeReviewReturned($query)
    {
        return $query->where('review_status', self::REVIEW_RETURNED);
    }

    /**
     * True for questions created by the assessment checklist flow, false
     * for freeform ad-hoc questions asked outside it.
     */
    public function isChecklistItem(): bool
    {
        return $this->review_status !== null;
    }

    /**
     * The checklist item 'type' (upload_comment|comment|upload|bank_connect)
     * for this question's doc_category_hint, or null if it isn't a
     * recognised checklist item (freeform questions, or a hint that no
     * longer exists in the config).
     */
    public function checklistItemType(): ?string
    {
        if (! $this->isChecklistItem()) {
            return null;
        }

        return collect(config('assessment_checklist.items'))
            ->firstWhere('slug', $this->doc_category_hint)['type'] ?? null;
    }

    /**
     * True when the client needs to act: either they've never submitted
     * (review_status = pending) or the admin sent it back (returned).
     */
    public function needsClientAction(): bool
    {
        return in_array($this->review_status, [self::REVIEW_PENDING, self::REVIEW_RETURNED], true);
    }

    /**
     * Approve a checklist item. Stamps read_by/read_at too (if unset) so a
     * review decision also clears the "unread answered question" count —
     * there's no separate "mark read" step in the checklist review flow.
     */
    public function markApproved(int $userId): void
    {
        $this->update([
            'review_status' => self::REVIEW_APPROVED,
            'reviewed_by'   => $userId,
            'reviewed_at'   => now(),
            'read_by'       => $this->read_by ?? $userId,
            'read_at'       => $this->read_at ?? now(),
        ]);
    }

    /**
     * Return a checklist item to the client for correction.
     */
    public function markReturned(int $userId, string $notes): void
    {
        $this->update([
            'review_status' => self::REVIEW_RETURNED,
            'review_notes'  => $notes,
            'reviewed_by'   => $userId,
            'reviewed_at'   => now(),
            'read_by'       => $this->read_by ?? $userId,
            'read_at'       => $this->read_at ?? now(),
        ]);
    }
}
