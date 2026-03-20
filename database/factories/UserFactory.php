<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * O modelo que esta factory cria.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Senha padrão usada pela factory.
     */
    protected static ?string $password;

    /**
     * Define o estado padrão do modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(), // <- usa $this->faker corretamente
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('Mudar@123'), // padrão do seu projeto
            'remember_token' => Str::random(10),
            'access_level' => 0, // padrão é 0
            'is_active' => true,  // ativo por padrão
        ];
    }

    /**
     * Indica que o email do usuário não foi verificado.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Permite criar usuário admin (nível 1) facilmente.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => 1,
        ]);
    }

    /**
     * Permite criar usuário inativo facilmente.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}