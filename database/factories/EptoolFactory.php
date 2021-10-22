<?php

namespace Database\Factories;

use App\Models\Eptool;
use Illuminate\Database\Eloquent\Factories\Factory;

class EptoolFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Eptool::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(),
            'image_id' => $this->faker->sentence(),
        ];
    }
}
