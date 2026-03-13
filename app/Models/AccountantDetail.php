<?php
// app/Models/AccountantDetail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountantDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'accountant_name',
        'accountant_email',
        'accountant_phone',
        'years_with_accountant',
    ];

    protected $casts = [
        'years_with_accountant' => 'integer',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
