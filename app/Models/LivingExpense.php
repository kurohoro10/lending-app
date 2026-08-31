<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivingExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'expense_category',
        'expense_name',
        'client_declared_amount',
        'provider_amount',
        'verified_amount',
        'frequency',
        'client_notes',
        'assessor_notes',
        'verification_notes',
        'verified_by',
        'verified_at',
        'is_verified',
    ];

    protected $casts = [
        'client_declared_amount' => 'decimal:2',
        'provider_amount'        => 'decimal:2',
        'verified_amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getMonthlyAmount(): float
    {
        $amount = (float)($this->verified_amount ?? $this->client_declared_amount);

        return match($this->frequency) {
            'weekly' => $amount * 52 / 12,
            'fortnightly' => $amount * 26 / 12,
            'monthly' => $amount,
            'quarterly' => $amount * 4 / 12,
            'annual' => $amount / 12,
            default => 0,
        };
    }

    public function getAnnualAmount(): float
    {
        return $this->getMonthlyAmount() * 12;
    }

    public static function getExpenseCategories(): array
    {
        return [
            'housing'    => 'Housing and Utilities',
            'internet'   => 'Internet Telephone and Pay TV',
            'groceries'  => 'Groceries',
            'recreation' => 'Recreation and Entertainment',
            'clothing'   => 'Clothing and Personal Care',
            'medical'    => 'Medical and Health',
            'transport'  => 'Transport',
            'education'  => 'Education and Childcare',
            'insurance'  => 'Insurance',
            'other'      => 'Other Expenses',
        ];
    }

    public function latestVerificationNote(): ?string
    {
        return $this->application
            ->comments()
            ->where('type', 'internal')
            ->where('comment', 'like', '[Expense Verification] ' . $this->expense_name . ':%')
            ->latest()
            ->value('comment');
    }

    public function latestVerificationNoteText(): ?string
    {
        $raw = $this->latestVerificationNote();
        if (!$raw) return null;

        // Split on first ": " — everything after is the note text
        $pos = strpos($raw, ': ');
        return $pos !== false ? substr($raw, $pos + 2) : $raw;
    }
}
