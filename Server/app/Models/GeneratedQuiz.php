<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeneratedQuiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_material_id',
        'material_version',
        'generated_by',
        'ai_provider',
        'ai_model',
        'question_count',
        'source_char_count',
        'generation_time_ms',
    ];

    protected $casts = [
        'material_version' => 'integer',
        'question_count' => 'integer',
        'source_char_count' => 'integer',
        'generation_time_ms' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (GeneratedQuiz $quiz) {
            // Clean up polymorphic bookmarks to prevent orphans
            $quiz->bookmarks()->delete();
        });
    }

    public function courseMaterial(): BelongsTo
    {
        return $this->belongsTo(CourseMaterial::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id');
    }

    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }
}
