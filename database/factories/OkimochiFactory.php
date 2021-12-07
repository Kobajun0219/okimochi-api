<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OkimochiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'who' => $this->faker->name(),
            'title' => $this->faker->realText(rand(10, 20)),
            'message' => $this->faker->realText(rand(10, 50)),
            'user_name' => $this->faker->name(),
            'user_id' => $this->faker->numberBetween(1,3),
            'pic_name' => $this->faker->domainName(),
            'open_time' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
            'open_place_name' => $this->faker->city(),
            'open_place_latitude' => $this->faker->latitude(),
            'open_place_longitude' => $this->faker->longitude(),
            'public' => $this->faker->numberBetween(0, 1),
        ];
    }
}
