<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'user_id' => 1,
            'recipients_list_id' => 1,
            'message_id' => 1,
            'sent_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'clicked_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'total_recipients_click_thru' => 1,
            'status' => 1,
            'message_body' => $this->faker->text,
            'recipient_phone' => $this->faker->phoneNumber,
            'is_downloaded_as_csv' => 1,
            'contact_id' => 1,
            'campaign_id' => 1,
            'batch' => 1,
            'is_sent' => 1,
            'is_click' => 1,
            'is_bot' => 1,
            'is_unique_global' => 1,
            'is_unique_campaign' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
