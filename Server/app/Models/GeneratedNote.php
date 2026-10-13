<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_material_id',
        'material_version',
        'generated_by',
        'content',
        'ai_provider',
        'ai_model',
        'source_char_count',
        'notes_char_count',
        'generation_time_ms',
    ];

    protected $casts = [
        'material_version' => 'integer',
        'source_char_count' => 'integer',
        'notes_char_count' => 'integer',
        'generation_time_ms' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (GeneratedNote $note) {
            // Clean up polymorphic bookmarks to prevent orphans
            $note->bookmarks()->delete();
        });
    }

    /**
     * Get the course material this note was generated from.
     */
    public function courseMaterial(): BelongsTo
    {
        return $this->belongsTo(CourseMaterial::class);
    }

    /**
     * Get the user who generated this note.
     */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }
}
