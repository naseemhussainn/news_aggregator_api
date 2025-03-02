<?php
namespace Database\Factories;

use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

class SourceFactory extends Factory
{
    protected $model = Source::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'api_id' => $this->faker->uuid,
            'url' => $this->faker->url,
            'api_provider' => $this->faker->word,
        ];
    }
}