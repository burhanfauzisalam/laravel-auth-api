<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MtokenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'duration' => 60, // nilai default (boleh diubah)
        ];
    }
}