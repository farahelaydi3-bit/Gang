<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestoreCourtBookDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();

            DB::table('super_admins')->updateOrInsert(
                ['email' => 'owner@courtbook.com'],
                [
                    'name' => 'CourtBook Owner',
                    'password' => '$2y$12$xhz886ZnGb5PSnV2J2mAtOz5dmlKQgFZvdU05iXpPxWubg.UeCLmq',
                    'phone' => '0790000000',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
            $superAdminId = DB::table('super_admins')
                ->where('email', 'owner@courtbook.com')
                ->value('id');

            DB::table('admins')->updateOrInsert(
                ['email' => 'admin@courtbook.com'],
                [
                    'super_admin_id' => $superAdminId,
                    'name' => 'CourtBook Admin',
                    'password' => '$2y$12$AHOBnmduFL2bd63AlmBa5.gNNdN3/N5XVWPQ7w4lS6MEoEa4jeZmK',
                    'phone' => '0791111111',
                    'business_name' => 'CourtBook Sports',
                    // Admin accounts use active/pending/rejected/suspended in this schema.
                    'status' => 'active',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
            $adminId = DB::table('admins')
                ->where('email', 'admin@courtbook.com')
                ->value('id');

            $sportIds = [];
            foreach (['Football', 'Basketball', 'Padel', 'Tennis', 'Badminton'] as $name) {
                DB::table('sports')->updateOrInsert(
                    ['name' => $name],
                    ['updated_at' => $now, 'created_at' => $now]
                );
                $sportIds[$name] = DB::table('sports')->where('name', $name)->value('id');
            }

            $courts = [
                ['Football', 'Aldaoud goal space', 'شارع شفا بدران', 'Aldaoud goal space.png', '09:00:00', '01:00:00', 25, 30, 35, 10],
                ['Football', 'Jordan Galaxy Stadium', 'نلتشيك، عمان', 'Jordan Galaxy Stadium.png', '09:00:00', '01:00:00', 25, 30, 35, 10],
                ['Football', 'Mayar Football Pitch', 'XVVJ+8CF، عمان', 'football-indoor.png', '09:00:00', '01:00:00', 20, 25, 30, 10],
                ['Football', 'KHBP Football Field', 'شارع الملك عبدالله الثاني', 'KHBP Football Field.png', '09:00:00', '01:00:00', 25, 30, 35, 10],
                ['Football', 'Ayla International School Fields', 'طبربور، عمان', 'football-indoor.png', '08:00:00', '22:00:00', 20, 25, 30, 10],
                ['Football', 'Armed Forces Fields', 'طبربور، عمان', 'PASS soccer field.png', '08:00:00', '23:00:00', 20, 25, 30, 10],
                ['Football', 'Al Yarmouk Sports Fields', 'شارع الاستقلال 90، عمان', 'PASS soccer field.png', '08:00:00', '23:00:00', 20, 25, 30, 10],
                ['Football', 'Al Mahatta Stadium', 'XX55+V39، عمان', 'PASS soccer field.png', '09:00:00', '00:00:00', 20, 25, 30, 10],
                ['Basketball', 'Kings Basketball Academy - Jubaiha', 'الجبيهة، عمان', 'basketball.png', '09:00:00', '22:00:00', 15, 20, 25, 5],
                ['Basketball', 'Kings Basketball Academy - Shmeisani', 'عبدالله بن مسعود، الشميساني', 'basketball.png', '09:00:00', '22:00:00', 15, 20, 25, 5],
                ['Basketball', 'SD13 Sports Academy', 'عمان', 'basketball.png', '09:00:00', '22:00:00', 15, 20, 25, 5],
                ['Basketball', 'Wolves Academy', 'شارع بهجت التلهوني، عمان', 'basketball.png', '09:00:00', '22:00:00', 15, 20, 25, 5],
                ['Basketball', 'Al Hussein Park Basketball Court', 'XRRH+95J، عمان', 'basketball.png', '08:00:00', '22:00:00', 10, 15, 20, 5],
                ['Padel', 'The Padel Hall', 'شارع الملك عبدالله الثاني، عمان', 'The Padel Hall.png', '08:00:00', '00:00:00', 20, 25, 30, 10],
                ['Padel', 'Padel Mania by The Padelist', 'ممر عبدون، عمان', 'Padel Mania by The Padelist.png', '08:00:00', '00:00:00', 25, 30, 35, 10],
                ['Padel', 'Padel Arena Jo', 'شارع سليمان عودة السرور، عمان', 'Padel Arena Jo.png', '08:00:00', '00:00:00', 20, 25, 30, 10],
                ['Padel', 'Project Padel', 'حديقة الملك حسين للأعمال، بوابة 4', 'Project Padel.png', '08:00:00', '00:00:00', 25, 30, 35, 10],
                ['Padel', 'Padel Center', 'ممر عبدون، عمان', 'Padel Center.png', '08:00:00', '00:00:00', 20, 25, 30, 10],
                ['Tennis', 'Base Tennis Jo', 'كفور الإخشيدي، عمان', 'Base Tennis Jo.png', '08:00:00', '23:00:00', 15, 20, 25, 5],
                ['Tennis', 'Modern Tennis Academy', 'دوار الأمير زيد بن شاكر، عمان', 'Modern Tennis Academy.png', '08:00:00', '23:00:00', 15, 20, 25, 5],
                ['Tennis', 'Jordan Tennis Federation', 'شارع الشهيد، عمان', 'tennis.png', '08:00:00', '22:00:00', 15, 20, 25, 5],
                ['Tennis', 'Naffa Tennis Academy', 'XWJ4+7C3، عمان', 'tennis.png', '08:00:00', '22:00:00', 15, 20, 25, 5],
                ['Tennis', 'Jordan Tennis Club', 'دوار الأمير زيد بن شاكر، عمان', 'tennis.png', '08:00:00', '23:00:00', 15, 20, 25, 5],
                ['Badminton', 'Shuttlers Jordan Badminton Academy', 'شارع المحمدية، عمان', 'Shuttlers Jordan Badminton Academy.png', '09:00:00', '22:00:00', 10, 15, 20, 5],
                ['Badminton', 'Dopamine Badminton Academy', 'دوار الرابية، عمان', 'badminton.png', '09:00:00', '22:00:00', 10, 15, 20, 5],
                ['Badminton', 'YOLO Badminton Academy', 'شارع ابن العربي، عمان', 'badminton.png', '09:00:00', '22:00:00', 10, 15, 20, 5],
                ['Badminton', 'Elite Badminton Academy', 'شارع عبدالكريم الدباس، عمان', 'badminton.png', '09:00:00', '22:00:00', 10, 15, 20, 5],
                ['Badminton', 'GIOKO Badminton Academy', 'شارع الحجاز، السويلح', 'badminton.png', '09:00:00', '22:00:00', 10, 15, 20, 5],
            ];

            foreach ($courts as [$sport, $name, $address, $image, $opens, $closes, $weekday, $weekend, $evening, $deposit]) {
                DB::table('courts')->updateOrInsert(
                    ['name' => $name],
                    [
                        'admin_id' => $adminId,
                        'sport_id' => $sportIds[$sport],
                        'address' => $address,
                        'image' => $image,
                        'opening_time' => $opens,
                        'closing_time' => $closes,
                        'min_booking_minutes' => 60,
                        'weekday_price' => $weekday,
                        'weekend_price' => $weekend,
                        'evening_price' => $evening,
                        'deposit_amount' => $deposit,
                        'status' => 'approved',
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        });
    }
}
