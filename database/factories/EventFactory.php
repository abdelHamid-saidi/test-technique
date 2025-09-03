<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->jobTitle(),
            'starts_at' => $this->faker->dateTimeThisYear(),

            // ajout d'une valeur pour ends_at
            'ends_at' => $this->faker->dateTimeThisYear(),

            // Récupérer un user_id de la base de données
            'user_id' => User::first()->id ?? User::factory(),
        ];
    }
}
