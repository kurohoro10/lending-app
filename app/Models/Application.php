<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'application_number',
        'status',
        'loan_amount',
        'loan_purpose',
        'loan_purpose_details',
        'term_months',
        'security_type',
        'submitted_at',
        'completed_at',
        'submission_ip',
        'electronic_signature_id',
        'signature_signed_at',
        'signature_ip',
        'assigned_to',
    ];

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'signature_signed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($application) {
            if (empty($application->application_number)) {
                $application->application_number = 'APP-' . date('Y') . '-' . str_pad(
                    static::whereYear('created_at', date('Y'))->count() + 1,
                    6,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function personalDetails(): HasOne
    {
        return $this->hasOne(PersonalDetail::class);
    }

    public function residentialAddresses(): HasMany
    {
        return $this->hasMany(ResidentialAddress::class);
    }

    public function employmentDetails(): HasMany
    {
        return $this->hasMany(EmploymentDetail::class);
    }

    public function livingExpenses(): HasMany
    {
        return $this->hasMany(LivingExpense::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function communications(): HasMany
    {
        return $this->hasMany(Communication::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function declarations(): HasMany
    {
        return $this->hasMany(Declaration::class);
    }

    public function creditChecks(): HasMany
    {
        return $this->hasMany(CreditCheck::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    // Helper methods
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'additional_info_required']);
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === 'draft' &&
               $this->hasCompletePersonalDetails() &&
               $this->residentialAddresses()->count() > 0 &&
               $this->employmentDetails()->exists();
    }

    public function getTotalLivingExpensesMonthly(): float
    {
        return $this->livingExpenses->sum(function ($expense) {
            return $expense->getMonthlyAmount();
        });
    }

    public function getAnnualIncome(): float
    {
        return $this->employmentDetails->sum(function ($employment) {
            return $employment->getAnnualIncome();
        });
    }

    public function hasCompletePersonalDetails(): bool
    {
        if (!$this->personalDetails) {
            return false;
        }

        $pd = $this->personalDetails;

        return !empty($pd->full_name)
            && !empty($pd->email)
            && !empty($pd->mobile_phone)
            && !empty($pd->date_of_birth)
            && !empty($pd->gender)
            && !empty($pd->marital_status);
    }
}
