<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentDetailHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'employment_detail_id',
        'changed_by',
        'field',
        'old_value',
        'new_value',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function employment(): BelongsTo
    {
        return $this->belongsTo(EmploymentDetail::class, 'employment_detail_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getFieldLabelAttribute(): string
    {
        return match($this->field) {
            'employment_type'             => 'Employment Type',
            'employer_business_name'      => 'Employer',
            'abn'                         => 'ABN',
            'employment_role'             => 'Role',
            'position'                    => 'Position',
            'employment_start_date'       => 'Start Date',
            'length_of_employment_months' => 'Length (months)',
            'base_income'                 => 'Base Income',
            'additional_income'           => 'Additional Income',
            'income_frequency'            => 'Income Frequency',
            'employer_phone'              => 'Employer Phone',
            'employer_address'            => 'Employer Address',
            default                       => ucfirst(str_replace('_', ' ', $this->field)),
        };
    }
}