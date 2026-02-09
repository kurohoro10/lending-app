<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Declaration extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'declaration_type',
        'declaration_text',
        'is_agreed',
        'agreed_at',
        'agreement_ip',
        'signature',
    ];

    protected $casts = [
        'is_agreed' => 'boolean',
        'agreed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function agree(?string $signature = null): void
    {
        $this->update([
            'is_agreed' => true,
            'agreed_at' => now(),
            'agreement_ip' => request()->ip(),
            'signature' => $signature,
        ]);
    }

    public static function getDefaultDeclarations(): array
    {
        return [
            'privacy' => 'I consent to my personal information being collected, used, and disclosed in accordance with the Privacy Policy.',
            'terms' => 'I have read, understood, and agree to the Terms and Conditions.',
            'accuracy' => 'I declare that all information provided in this application is true, accurate, and complete to the best of my knowledge.',
            'credit_check' => 'I authorize the lender to conduct credit checks and verify the information provided in this application.',
        ];
    }
}
