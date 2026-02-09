<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'application_id',
        'uploaded_by',
        'document_category',
        'document_type',
        'original_filename',
        'stored_filename',
        'file_path',
        'mime_type',
        'file_size',
        'description',
        'version',
        'parent_document_id',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'upload_ip',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function parentDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'parent_document_id');
    }

    public function getFileSizeHumanAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('documents.download', $this->id);
    }

    public function getFileIconAttribute(): string
    {
        return match(true) {
            str_contains($this->mime_type, 'pdf') => 'file-pdf',
            str_contains($this->mime_type, 'image') => 'file-image',
            str_contains($this->mime_type, 'word') || str_contains($this->mime_type, 'document') => 'file-word',
            str_contains($this->mime_type, 'excel') || str_contains($this->mime_type, 'spreadsheet') => 'file-excel',
            default => 'file',
        };
    }

    public static function getDocumentCategories(): array
    {
        return [
            'id' => 'Identification',
            'income' => 'Income Documentation',
            'bank' => 'Bank Statements',
            'assets' => 'Asset Documentation',
            'liabilities' => 'Liability Documentation',
            'employment' => 'Employment Verification',
            'other' => 'Other Documents',
        ];
    }

    public function delete()
    {
        // Delete physical file when soft deleting
        if (Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }

        return parent::delete();
    }
}
