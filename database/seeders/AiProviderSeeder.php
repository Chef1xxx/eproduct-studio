<?php

namespace Database\Seeders;

use App\Models\AiProvider;
use Illuminate\Database\Seeder;

class AiProviderSeeder extends Seeder
{
    public function run(): void
    {
        AiProvider::query()->firstOrCreate(
            ['driver' => 'gigachat'],
            [
                'name' => 'GigaChat',
                'model' => 'GigaChat-2-Max',
                'scope' => 'GIGACHAT_API_PERS',
                'is_active' => true,
            ],
        );
    }
}
