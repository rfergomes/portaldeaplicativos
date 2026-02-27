<?php

namespace Database\Factories;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    protected $model = Evento::class;

    public function definition(): array
    {
        $data = $this->faker->dateTimeBetween('+1 day', '+1 month');

        return [
            'nome' => 'Evento ' . $this->faker->words(2, true),
            'data_inicio' => $data,
            'local' => $this->faker->city,
            'valor_inteira' => 100,
            'valor_meia' => 50,
            'encerrado' => false,
        ];
    }
}

