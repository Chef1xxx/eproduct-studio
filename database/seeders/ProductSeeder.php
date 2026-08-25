<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'demo@eproduct.local'],
            [
                'name' => 'Demo Seller',
                'password' => 'password',
            ],
        );

        $categories = Category::query()->get();

        if ($categories->isEmpty()) {
            $this->command?->warn('Нет категорий. Сначала запусти CategorySeeder.');

            return;
        }

        if (Product::query()->where('user_id', $user->id)->exists()) {
            return;
        }

        Product::factory()
            ->count(6)
            ->create([
                'user_id' => $user->id,
                'category_id' => fn () => $categories->random()->id,
            ]);
    }
}