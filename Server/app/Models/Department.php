<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['university_id', 'college', 'name', 'code', 'total_years', 'is_active'])]
class Department extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'total_years' => 'integer',
        ];
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function courseOfferings()
    {
        return $this->hasMany(CourseOffering::class);
    }

    public function activeCourseOfferings()
    {
        return $this->hasMany(CourseOffering::class)->where('is_active', true);
    }
}
