<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'application_id',
        'type',
        'title',
        'message',
        'data',
        'action_url',
        'is_read',
        'read_at',
        'send_email',
        'send_sms',
    ];

    protected $casts = [
        'data' => 'array', // Crucial for JSON columns
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'sms_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
