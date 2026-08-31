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

    // =========================================================================
    // Status Constants
    // =========================================================================
    public const STATUS_APPLICATION = 'application';
    public const STATUS_WIP = 'wip';
    public const STATUS_OUTDOC = 'outdoc';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_DEFERRED = 'deferred';
    public const STATUS_SETTLED = 'settled';

    // All valid statuses
    public const VALID_STATUSES = [
        self::STATUS_APPLICATION,
        self::STATUS_WIP,
        self::STATUS_OUTDOC,
        self::STATUS_APPROVED,
        self::STATUS_DECLINED,
        self::STATUS_DEFERRED,
        self::STATUS_SETTLED,
    ];

    // Workflow progression order
    public const STATUS_WORKFLOW = [
        self::STATUS_APPLICATION,
        self::STATUS_WIP,
        self::STATUS_OUTDOC,
        self::STATUS_APPROVED,
        self::STATUS_SETTLED,
    ];

    // Terminal statuses (no further transitions)
    public const TERMINAL_STATUSES = [
        self::STATUS_DECLINED,
        self::STATUS_SETTLED,
    ];

    // Returnable statuses (can be returned to client)
    public const RETURNABLE_STATUSES = [
        self::STATUS_WIP,
        self::STATUS_OUTDOC,
    ];

    const ADMIN_TABS = [
        'in_progress' => [
            self::STATUS_APPLICATION,
            self::STATUS_WIP,
            self::STATUS_OUTDOC,
        ],
        'approve' => [self::STATUS_APPROVED],
        'deferred' => [self::STATUS_DEFERRED],
        'declined' => [self::STATUS_DECLINED],
        'settled' => [self::STATUS_SETTLED],
    ];

    public function getTabForStatus(string $status): ?string
    {
        foreach (self::ADMIN_TABS as $tab => $statuses) {
            if (in_array($status, $statuses)) {
                return $tab;
            }
        }
        return null;
    }

    protected $fillable = [
        'user_id',
        'application_number',
        'status',
        'loan_amount',
        'loan_purpose',
        'loan_purpose_details',
        'term_weeks',
        'security_type',
        'submitted_at',
        'completed_at',
        'submission_ip',
        'bank_api_provider_name',
        'bank_api_user_ref',
        'bank_api_phone_used',
        'bank_api_completed_at',
        'bank_api_report',
        'bank_api_report_received_at',
        'return_reason',
        'returned_at',
        'returned_by',
        'electronic_signature_id',
        'signature_signed_at',
        'signature_ip',
        'assigned_to',
        'verified_expenses',
        'credit_sense_app_id',
        'credit_sense_completed_at',
        'credit_sense_report',
        'credit_sense_report_received_at',
        'guarantor_form_generated_at',
        'guarantor_form_path',
        // NEW COLUMNS
        'approval_letter_sent_at',
        'guarantor_form_requested_at',
        'guarantor_form_request_url',
        'guarantor_form_completed_at',
        'guarantor_form_signed_at',
        'business_declaration_sent_at',
        'business_declaration_signed_at',
        'decline_letter_sent_at',
        'decline_reason',
        'guarantor_data',
        'guarantor_required',
        'loan_deed_data',
        'loan_deed_requested_at',
        'loan_deed_request_url',
        'loan_deed_signed_at',
        'business_declaration_data',
        'business_declaration_requested_at',
        'document_signing_file_path',
        'document_signing_data',
    ];

    protected $casts = [
        'loan_amount'                      => 'decimal:2',
        'submitted_at'                     => 'datetime',
        'completed_at'                     => 'datetime',
        'signature_signed_at'              => 'datetime',
        'returned_at'                      => 'datetime',
        'credit_sense_completed_at'        => 'datetime',
        'credit_sense_report'              => 'array',
        'credit_sense_report_received_at'  => 'datetime',
        'verified_expenses'                => 'array',
        'guarantor_form_generated_at'      => 'datetime',
        // NEW COLUMNS
        'approval_letter_sent_at'          => 'datetime',
        'guarantor_form_requested_at'      => 'datetime',
        'guarantor_form_completed_at'      => 'datetime',
        'guarantor_form_signed_at'         => 'datetime',
        'business_declaration_sent_at'     => 'datetime',
        'business_declaration_signed_at'   => 'datetime',
        'decline_letter_sent_at'           => 'datetime',
        'guarantor_data'                   => 'array',
        'loan_deed_data'                   => 'array',
        'loan_deed_requested_at'           => 'datetime',
        'loan_deed_signed_at'              => 'datetime',
        'business_declaration_data'        => 'array',
        'business_declaration_requested_at' => 'datetime',
        'document_signing_data'            => 'array',
        'bank_api_report_received_at' => 'datetime',
        'bank_api_completed_at'       => 'datetime',
        'credit_sense_report_received_at' => 'datetime',
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

    // =========================================================================
    // Relationships
    // =========================================================================
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function borrowerInformation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\BorrowerInformation::class);
    }

    public function borrowerDirectors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BorrowerDirector::class);
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

    /**
     * Assessment checklist completion for the client progress indicator.
     * "Completed" = the client has nothing left to do on that item right
     * now (review_status is awaiting_review or approved) — pending/returned
     * items still need the client's action. Freeform questions (review_status
     * null) are excluded entirely, so this only ever reflects the checklist.
     */
    public function assessmentChecklistProgress(): array
    {
        $items = $this->questions->whereNotNull('review_status');

        return [
            'total'     => $items->count(),
            'completed' => $items->whereNotIn('review_status', [
                Question::REVIEW_PENDING,
                Question::REVIEW_RETURNED,
            ])->count(),
        ];
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

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function directorAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\DirectorAsset::class);
    }

    public function directorLiabilities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\DirectorLiability::class);
    }

    public function companyAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CompanyAsset::class);
    }

    public function companyLiabilities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CompanyLiability::class);
    }

    public function accountantDetail(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\AccountantDetail::class);
    }

    public function assessorDalVerification(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\AssessorDalVerification::class);
    }

    public function assessorEmploymentVerification(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\AssessorEmploymentVerification::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================
    public function scopeApplication($query)
    {
        return $query->where('status', self::STATUS_APPLICATION);
    }

    public function scopeWip($query)
    {
        return $query->where('status', self::STATUS_WIP);
    }

    public function scopeSettled($query)
    {
        return $query->where('status', self::STATUS_SETTLED);
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', self::STATUS_DECLINED);
    }

    public function scopeDeferred($query)
    {
        return $query->where('status', self::STATUS_DEFERRED);
    }

    public function scopeNotTerminal($query)
    {
        return $query->whereNotIn('status', self::TERMINAL_STATUSES);
    }

    // Legacy scopes (kept for backward compatibility)
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_APPLICATION);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_WIP);
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', self::STATUS_WIP);
    }

    // =========================================================================
    // Status & Workflow Helper Methods
    // =========================================================================

    /**
     * Check if application is in an editable state
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPLICATION,
            self::STATUS_OUTDOC,
        ]);
    }

    /**
     * Check if application is locked (terminal status)
     */
    public function isLocked(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES);
    }

    /**
     * Check if application is in approval phase (Settled)
     */
    public function isInApprovalPhase(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if application can generate guarantor form
     */
    public function canGenerateGuarantorForm(): bool
    {
        return $this->status === self::STATUS_APPROVED && !$this->guarantor_form_path;
    }

    /**
     * Check if guarantor form has been generated
     */
    public function hasGuarantorForm(): bool
    {
        return !is_null($this->guarantor_form_path) && file_exists(public_path($this->guarantor_form_path));
    }

    /**
     * Get status display label
     */
    public function getStatusLabel(): string
    {
        return self::statusLabel($this->status);
    }

    public static function statusLabel(string $status): string
    {
        $labels = [
            self::STATUS_APPLICATION => 'Application',
            self::STATUS_WIP         => 'Work in Progress',
            self::STATUS_OUTDOC      => 'Outstanding Document',
            self::STATUS_APPROVED    => 'Approved',
            self::STATUS_DECLINED    => 'Declined',
            self::STATUS_DEFERRED    => 'Deferred',
            self::STATUS_SETTLED     => 'Settled',
        ];

        return $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
    }

    /**
     * Get status badge color class (Tailwind)
     */
    public function getStatusBadgeColor(): string
    {
        return self::statusBadgeColor($this->status);
    }

    /**
     * Get the badge color for an arbitrary status value (not necessarily
     * this model's current status). Extracted from getStatusBadgeColor()
     * so historical status values (e.g. from an activity log entry) can be
     * colored identically to the live status badge shown elsewhere in the
     * admin UI, without duplicating the color map.
     */
    public static function statusBadgeColor(string $status): string
    {
        $colors = [
            self::STATUS_APPLICATION => 'blue',
            self::STATUS_WIP         => 'yellow',
            self::STATUS_OUTDOC      => 'orange',
            self::STATUS_APPROVED    => 'purple',
            self::STATUS_DECLINED    => 'red',
            self::STATUS_DEFERRED    => 'gray',
            self::STATUS_SETTLED     => 'green',
        ];

        return $colors[$status] ?? 'gray';
    }

    /**
     * Get workflow step number (1-7)
     */
    public function getWorkflowStep(): ?int
    {
        $index = array_search($this->status, self::STATUS_WORKFLOW);
        return $index !== false ? $index + 1 : null;
    }

    /**
     * Check if status can transition to another status
     */
    public function canTransitionTo(string $newStatus): bool
    {
        // Cannot transition from terminal status
        if ($this->isLocked()) {
            return false;
        }

        // Valid target status
        if (!in_array($newStatus, self::VALID_STATUSES)) {
            return false;
        }

        // Cannot transition to same status
        if ($newStatus === $this->status) {
            return false;
        }

        // Can only transition to returnable or workflow-next statuses
        $currentIndex = array_search($this->status, self::STATUS_WORKFLOW);
        $newIndex = array_search($newStatus, self::STATUS_WORKFLOW);

        // Allow forward progression in workflow
        if ($newIndex !== false && $currentIndex !== false && $newIndex >= $currentIndex) {
            return true;
        }

        // Allow transitions to terminal/exceptional statuses
        if (in_array($newStatus, [self::STATUS_DECLINED, self::STATUS_DEFERRED])) {
            return true;
        }

        return false;
    }

    /**
     * Check if application is returnable to client
     */
    public function isReturnable(): bool
    {
        return in_array($this->status, self::RETURNABLE_STATUSES);
    }

    /**
     * Legacy method - now checks if ready to move to Assessment
     */
    public function canBeSubmitted(): bool
    {
        return $this->canMoveToAssessment();
    }

    /**
     * Check if ready to move to Assessment
     */
    public function canMoveToAssessment(): bool
    {
        if ($this->status !== self::STATUS_APPLICATION) {
            return false;
        }

        $hasPersonalDetails = $this->personalDetails !== null;
        $hasResidentialAddresses = $this->residentialAddresses()->count() > 0;
        $hasEmploymentDetails = $this->employmentDetails()->count() > 0;
        $hasLivingExpenses = $this->livingExpenses()->count() > 0;
        $hasFinalSignature = $this->hasFinalSignature();

        return $hasPersonalDetails
            && $hasResidentialAddresses
            && $hasEmploymentDetails
            && $hasLivingExpenses
            && $hasFinalSignature;
    }

    /**
     * Check if application can proceed to Settled
     */
    public function canMoveToSettled(): bool
    {
        return $this->status === self::STATUS_APPROVED
            && $this->isApprovedPhaseComplete();
    }

    /**
     * Legacy method for backward compatibility
     */
    public function isReturned(): bool
    {
        return $this->status === self::STATUS_OUTDOC;
    }

    // =========================================================================
    // Application Data Helper Methods
    // =========================================================================

    /**
     * Check if application has required business info for auto-decline checks
     */
    public function hasBusinessInfo(): bool
    {
        return $this->employmentDetails()->count() > 0;
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

        return !empty($pd->mobile_phone)
            && !empty($pd->marital_status)
            && !is_null($pd->number_of_dependants)
            && !empty($pd->date_of_birth)
            && !empty($pd->citizenship_status);
    }

    /**
     * Check if application has final submission signature
     */
    public function hasFinalSignature(): bool
    {
        return $this->declarations()
            ->where('declaration_type', 'final_submission')
            ->where('is_agreed', true)
            ->whereNotNull('signature_data')
            ->exists();
    }

    /**
     * Check if approvalApproved workflow is complete
     */
    public function isApprovedPhaseComplete(): bool
    {
        return $this->status === self::STATUS_APPROVED
            && $this->approval_letter_sent_at
            && $this->guarantor_form_signed_at
            && $this->business_declaration_signed_at;
    }

    /**
     * Check if guarantor form has been requested
     */
    public function hasGuarantorFormRequested(): bool
    {
        return !is_null($this->guarantor_form_requested_at);
    }

    /**
     * Check if guarantor form has been completeds
     */
    public function hasGuarantorFormCompleted(): bool
    {
        return !is_null($this->guarantor_form_completed_at);
    }

    public function hasGuarantorData(): bool
    {
        return !is_null($this->guarantor_data);
    }

    public function isGuarantorFormSigned(): bool
    {
        return !is_null($this->guarantor_form_signed_at);
    }

    public function requiresGuarantor(): bool
    {
        return (bool) $this->guarantor_required;
    }

    public function hasLoanDeedData(): bool
    {
        return !is_null($this->loan_deed_data);
    }

    public function isLoanDeedSigned(): bool
    {
        return !is_null($this->loan_deed_signed_at);
    }

    public function hasBusinessDeclarationData(): bool
    {
        return !is_null($this->business_declaration_data);
    }

    public function hasDocumentSigningFile(): bool
    {
        return !is_null($this->document_signing_file_path);
    }

    public function isDocumentSigningSigned(): bool
    {
        return !empty($this->document_signing_data['signed_at'] ?? null);
    }
}