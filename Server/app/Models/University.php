<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'code', 'is_active'])]
class University extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function activeDepartments()
    {
        return $this->hasMany(Department::class)->where('is_active', true);
    }
}
