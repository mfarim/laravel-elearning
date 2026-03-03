<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
  public function run(): void
  {
    $admin = User::create([
      'name' => 'Administrator',
      'email' => 'admin@sekolah.id',
      'password' => bcrypt('password'),
      'phone' => '081234567890',
      'is_active' => true,
    ]);

    $admin->assignRole('admin');
  }
}
