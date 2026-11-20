<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'booking_id' => function (array $attributes) {
                $superAdminId = DB::table('super_admins')->insertGetId([
                    'name' => 'Test Super Admin',
                    'email' => Str::uuid() . '@example.com',
                    'password' => Hash::make('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $adminId = DB::table('admins')->insertGetId([
    'super_admin_id' => $superAdminId,
    'name' => 'Test Admin',
    'email' => Str::uuid() . '@example.com',
    'phone' => '0791234567',
    'business_name' => 'Test Sports Center',
    'password' => Hash::make('password'),
    'status' => 'active',
    'created_at' => now(),
    'updated_at' => now(),
]);

                $sportId = DB::table('sports')->insertGetId([
                    'name' => 'Test Sport ' . Str::random(8),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $courtId = DB::table('courts')->insertGetId([
                    'admin_id' => $adminId,
                    'sport_id' => $sportId,
                    'name' => 'Test Court',
                    'address' => 'Test Address',
                    'opening_time' => '08:00:00',
                    'closing_time' => '23:00:00',
                    'min_booking_minutes' => 60,
                    'weekday_price' => 20,
                    'weekend_price' => 25,
                    'evening_price' => 30,
                    'deposit_amount' => 5,
                    'status' => 'approved',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return DB::table('bookings')->insertGetId([
                    'court_id' => $courtId,
                    'user_id' => $attributes['user_id'],
                    'booking_type' => 'individual',
                    'booking_date' => now()->subDay()->toDateString(),
                    'start_time' => '10:00:00',
                    'end_time' => '11:00:00',
                    'duration_minutes' => 60,
                    'total_price' => 20,
                    'deposit_amount' => 5,
                    'booking_status' => 'completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            },

            'court_id' => function (array $attributes) {
                return DB::table('bookings')
                    ->where('id', $attributes['booking_id'])
                    ->value('court_id');
            },

            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->sentence(),
        ];
    }
}
