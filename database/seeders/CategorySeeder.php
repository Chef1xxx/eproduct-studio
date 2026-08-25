<?php
namespace Database\Seeders;
use App\Models\Category;
use Illuminate\Database\Seeder;
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Электроника', 'slug' => 'electronics'],
            ['name' => 'Одежда', 'slug' => 'clothing'],
            ['name' => 'Дом и сад', 'slug' => 'home-garden'],
        ];
        foreach ($categories as $category) {
            Category::query()->firstOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name']],
            );
        }
    }
}