<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mtoken;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $durations = [
            60,        // 1 jam
            1440,      // 24 jam
            4320,      // 3 hari
            10080,     // 7 hari
            43200,     // 30 hari
        ];

        foreach ($durations as $duration) {
            Mtoken::create([
                'duration' => $duration,
            ]);
        }
    }
}