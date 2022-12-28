<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Tag;

class TagFactory extends Factory
{
    /** @var string */
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'workspace_id' => 0,
            'name' => ucwords($this->faker->unique()->word)
        ];
    }
}
