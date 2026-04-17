<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin account ──────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['phone' => '07700000000'],
            [
                'name'           => 'المدير العام',
                'password'       => Hash::make('admin123'),
                'city'           => 'بغداد',
                'role'           => 'admin',
                'status'         => 'active',
                'phone_verified' => true,
            ]
        );

        // ── Test reseller account ──────────────────────────────────
        $reseller = User::firstOrCreate(
            ['phone' => '07711111111'],
            [
                'name'           => 'بائع تجريبي',
                'password'       => Hash::make('reseller123'),
                'city'           => 'بغداد',
                'role'           => 'reseller',
                'status'         => 'active',
                'phone_verified' => true,
            ]
        );

        // Create wallet for reseller if not exists
        if (!$reseller->wallet) {
            Wallet::create(['user_id' => $reseller->id]);
        }

        // ── Default categories ─────────────────────────────────────
        $categories = [
            ['name' => 'Clothes',      'name_ar' => 'ملابس',       'icon' => '👗', 'sort_order' => 1],
            ['name' => 'Electronics',  'name_ar' => 'إلكترونيات',  'icon' => '📱', 'sort_order' => 2],
            ['name' => 'Home',         'name_ar' => 'أدوات منزلية','icon' => '🏠', 'sort_order' => 3],
            ['name' => 'Cosmetics',    'name_ar' => 'مستحضرات',    'icon' => '💄', 'sort_order' => 4],
            ['name' => 'Sports',       'name_ar' => 'رياضة',       'icon' => '⚽', 'sort_order' => 5],
            ['name' => 'Accessories',  'name_ar' => 'اكسسوارات',   'icon' => '💍', 'sort_order' => 6],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], array_merge($cat, ['is_active' => true]));
        }

        $this->command->info('✅ Seeder completed:');
        $this->command->info('   Admin   → phone: 07700000000 | pass: admin123');
        $this->command->info('   Reseller→ phone: 07711111111 | pass: reseller123');
    }
}
