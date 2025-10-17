<?php

namespace Database\Factories\Jiny\Mail\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jiny\Mail\Models\AuthMailTemplate;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Jiny\Mail\Models\AuthMailTemplate>
 */
class AuthMailTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AuthMailTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = array_keys(AuthMailTemplate::getTypeOptions());

        return [
            'name' => fake()->sentence(3),
            'type' => fake()->randomElement($types),
            'subject' => fake()->sentence(),
            'message' => fake()->paragraphs(3, true),
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(80), // 80% 활성화
            'admin_user_id' => null,
            'admin_user_name' => 'System',
        ];
    }

    /**
     * Indicate that the template is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the template is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set the template type.
     */
    public function type(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    /**
     * Set the admin user.
     */
    public function byAdmin(int $adminUserId, string $adminUserName): static
    {
        return $this->state(fn (array $attributes) => [
            'admin_user_id' => $adminUserId,
            'admin_user_name' => $adminUserName,
        ]);
    }
}