<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Communication extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'user_id',
        'type',
        'direction',
        'from_address',
        'to_address',
        'subject',
        'body',
        'metadata',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',
        'error_message',
        'external_id',
        'sender_ip',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeEmails($query)
    {
        return $query->whereIn('type', ['email_in', 'email_out']);
    }

    public function scopeSms($query)
    {
        return $query->whereIn('type', ['sms_in', 'sms_out']);
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function isEmail(): bool
    {
        return in_array($this->type, ['email_in', 'email_out']);
    }

    public function isSms(): bool
    {
        return in_array($this->type, ['sms_in', 'sms_out']);
    }

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }
}
