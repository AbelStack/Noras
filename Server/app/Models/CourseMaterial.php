<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'course_offering_id', 
    'uploaded_by', 
    'title', 
    'description', 
    'original_filename', 
    'stored_filename', 
    'file_path', 
    'file_size', 
    'mime_type', 
    'status', 
    'version',
    'replaces_material_id'
])]
class CourseMaterial extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (CourseMaterial $material) {
            // Clean up polymorphic bookmarks to prevent orphans
            $material->bookmarks()->delete();
        });
    }

    public function courseOffering()
    {
        return $this->belongsTo(CourseOffering::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function replaces()
    {
        return $this->belongsTo(CourseMaterial::class, 'replaces_material_id');
    }

    public function replacedBy()
    {
        return $this->hasMany(CourseMaterial::class, 'replaces_material_id');
    }

    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function masterNote()
    {
        return $this->hasOne(GeneratedNote::class)->latestOfMany();
    }

    public function masterQuiz()
    {
        return $this->hasOne(GeneratedQuiz::class)->latestOfMany();
    }

    public function generatedNotes()
    {
        return $this->hasMany(GeneratedNote::class);
    }

    public function generatedQuizzes()
    {
        return $this->hasMany(GeneratedQuiz::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
