<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BroadcastLog>
 */
class BroadcastLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string)Str::ulid(),
            'user_id' => 1,
            'recipients_list_id' => 1,
            'message_id' => 1,
            'sent_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'clicked_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'status' => 1,
            'message_body' => $this->faker->text,
            'recipient_phone' => $this->faker->phoneNumber,
            'is_downloaded_as_csv' => 1,
            'contact_id' => 1,
            'campaign_id' => 1,
            'batch' => 1,
            'is_sent' => rand(0, 1),
            'is_click' => rand(0, 1),
            'is_bot' => rand(0, 1),
            'is_unique_global' => 1,
            'is_unique_campaign' => 1,
            'created_at' => Carbon::now()->subDays(rand(1, 14)),
            'updated_at' => Carbon::now()->subDays(rand(1, 14)),
        ];
    }
}
