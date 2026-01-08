<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Admin User
        $admin = User::firstOrCreate(
            ['phone' => '0999999999'], 
            [
                'name' => 'Admin Controller',
                'email' => 'admin@example.com',
                'password' => Hash::make('12345678'),
            ]
        );

        // ALWAYS Ensure trial is set for this admin if not set (or reset it for testing)
        if (!$admin->trial_ends_at && !$admin->license_key) {
             $admin->update(['trial_ends_at' => now()->addDays(3)]);
        }
        // Force update for this task verification
        $admin->update(['trial_ends_at' => now()->addDays(3)]);

        $this->command->info("Admin User ensured: " . $admin->name . " (Phone: 0999999999)");

        // 2. Assign legacy files to Admin
        $updated = File::whereNull('user_id')->update(['user_id' => $admin->id]);
        
        $this->command->info("Assigned $updated existing files to Admin.");
    }
}
