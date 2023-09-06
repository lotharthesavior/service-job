<?php

namespace Database\Factories;

use App\Enums\ServiceJobStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceJob>
 */
class ServiceJobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'status' => $this->faker->randomElement(array_keys(ServiceJobStatus::getOptions())),
        ];
    }
}
