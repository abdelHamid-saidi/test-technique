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
        $minutes = [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55];

        // Générer une date de début avec des minutes multiples de 5
        $startDate = $this->faker->dateTimeThisYear();
        $startMinutes = $this->faker->randomElement($minutes);
        $startsAt = $startDate->setTime($startDate->format('H'), $startMinutes, 0);

        // Générer une date de fin qui soit postérieure à la date de début
        $endDate = $this->faker->dateTimeBetween($startsAt, '+1 month');
        $endMinutes = $this->faker->randomElement($minutes);
        $endsAt = $endDate->setTime($endDate->format('H'), $endMinutes, 0);

        // S'assurer que ends_at est bien postérieur à starts_at
        if ($endsAt <= $startsAt) {
            $endsAt = clone $startsAt;
            $endsAt->modify('+1 hour');
            $endsAt->setTime($endsAt->format('H'), $endMinutes, 0);
        }

        return [
            'title' => $this->faker->jobTitle(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'user_id' => User::first()->id ?? User::factory(),
        ];
    }
}
