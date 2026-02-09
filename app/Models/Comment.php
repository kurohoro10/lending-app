<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'application_id',
        'user_id',
        'comment',
        'type',
        'is_pinned',
        'ip_address',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeInternal($query)
    {
        return $query->where('type', 'internal');
    }

    public function scopeClientVisible($query)
    {
        return $query->where('type', 'client_visible');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
}
