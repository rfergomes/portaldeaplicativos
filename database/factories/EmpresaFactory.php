<?php

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    public function definition(): array
    {
        return [
            'razao_social' => $this->faker->company . ' LTDA',
            'nome_fantasia' => $this->faker->company,
            'cnpj' => $this->faker->unique()->numerify('##.###.###/####-##'),
            'email' => $this->faker->companyEmail,
            'telefone' => $this->faker->phoneNumber,
            'cidade' => $this->faker->city,
            'estado' => $this->faker->stateAbbr,
            'categoria' => 'Sindicato',
            'ativo' => true,
        ];
    }
}

