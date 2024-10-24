<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Display a warning message
        $this->command->warn("Warning: All existing users will be deleted.");

        // Delete all existing users
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Disable foreign key checks
        DB::table('users')->truncate(); // Truncate the users table
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Re-enable foreign key checks

        // Call the RolesAndPermissionsSeeder to ensure roles are seeded
        $this->call(RolesAndPermissionsSeeder::class);

        // Create an admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'), // You can modify the password as needed
        ]);

        // Assign the 'admin' role to the admin user
        $admin->assignRole('admin');

        // Create a regular user
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'), // You can modify the password as needed
        ]);

        // Assign the 'user' role to the regular user
        $user->assignRole('user');

        // Output a success message
        $this->command->info('Admin and regular users have been created successfully!');
    }
}
