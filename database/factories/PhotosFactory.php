<?php

namespace Database\Factories;

use App\Models\Gallery;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotosFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Photo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(),
            'gallery_id' => Gallery::factory(),
        ];
    }
}
