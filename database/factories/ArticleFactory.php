<?php
namespace Database\Factories;

use App\Models\Article;
use App\Models\Source;
use App\Models\Category;
use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'content' => $this->faker->text,
            'url' => $this->faker->url,
            'image_url' => $this->faker->imageUrl,
            'source_id' => Source::factory(),
            'category_id' => Category::factory(),
            'external_id' => $this->faker->uuid,
            'published_at' => $this->faker->dateTime,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Article $article) {
            $article->authors()->attach(Author::factory()->create());
        });
    }
}