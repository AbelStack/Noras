<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['code', 'title', 'description', 'credit_hours', 'is_active'])]
class Course extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'credit_hours' => 'integer',
        ];
    }

    public function offerings()
    {
        return $this->hasMany(CourseOffering::class);
    }

    public function activeOfferings()
    {
        return $this->hasMany(CourseOffering::class)->where('is_active', true);
    }
}
