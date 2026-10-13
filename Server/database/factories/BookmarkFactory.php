<?php

namespace Database\Factories;

use App\Models\Bookmark;
use App\Models\CourseMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookmarkFactory extends Factory
{
    protected $model = Bookmark::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'bookmarkable_type' => CourseMaterial::class,
            'bookmarkable_id' => \App\Models\CourseMaterial::factory(),
        ];
    }
}
