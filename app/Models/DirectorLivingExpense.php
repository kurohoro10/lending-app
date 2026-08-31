<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectorLivingExpense extends Model
{
    protected $fillable = [
        'application_id',
        'borrower_director_id',
        'expenses',
        'submitted_at',
        'submitted_ip',
    ];

    protected $casts = [
        'expenses'     => 'array',
        'submitted_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(BorrowerDirector::class, 'borrower_director_id');
    }
}