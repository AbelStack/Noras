<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyTask extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'completed',
        'due_date',
        'completed_at',
        'course_material_id',
        'generated_note_id',
        'generated_quiz_id',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'completed' => false,
    ];

    /**
     * Get the user who owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated course material (optional).
     */
    public function courseMaterial(): BelongsTo
    {
        return $this->belongsTo(CourseMaterial::class);
    }

    /**
     * Get the associated generated note (optional).
     */
    public function generatedNote(): BelongsTo
    {
        return $this->belongsTo(GeneratedNote::class);
    }

    /**
     * Get the associated generated quiz (optional).
     */
    public function generatedQuiz(): BelongsTo
    {
        return $this->belongsTo(GeneratedQuiz::class);
    }
}
