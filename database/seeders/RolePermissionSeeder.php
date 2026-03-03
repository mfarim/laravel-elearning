<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
  public function run(): void
  {
    // Reset cached roles and permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    $permissionGroups = [
      'user' => ['view', 'create', 'edit', 'delete'],
      'teacher' => ['view', 'create', 'edit', 'delete'],
      'student' => ['view', 'create', 'edit', 'delete', 'import'],
      'classroom' => ['view', 'create', 'edit', 'delete'],
      'subject' => ['view', 'create', 'edit', 'delete'],
      'assignment' => ['view', 'create', 'edit', 'delete', 'grade', 'submit'],
      'exam' => ['view', 'create', 'edit', 'delete', 'grade', 'take', 'monitor', 'force_finish', 'reset'],
      'question' => ['view', 'create', 'edit', 'delete', 'import'],
      'material' => ['view', 'create', 'edit', 'delete'],
      'analytics' => ['view', 'export'],
      'report' => ['view', 'export'],
      'settings' => ['view', 'edit', 'backup', 'restore'],
      'announcement' => ['view', 'create', 'edit', 'delete'],
      'print' => ['card', 'attendance', 'report', 'score'],
      'security' => ['view', 'block_ip', 'block_user', 'unblock'],
    ];

    // Create all permissions
    $allPermissions = [];
    foreach ($permissionGroups as $group => $actions) {
      foreach ($actions as $action) {
        $name = "{$group}.{$action}";
        Permission::create(['name' => $name]);
        $allPermissions[] = $name;
      }
    }

    // Create roles and assign permissions
    $admin = Role::create(['name' => 'admin']);
    $admin->givePermissionTo($allPermissions);

    // Guru permissions
    $guruGroups = ['material', 'assignment', 'exam', 'question', 'announcement', 'print'];
    $guruExtra = [
      'student.view',
      'classroom.view',
      'subject.view',
      'analytics.view',
      'analytics.export',
      'report.view',
      'report.export',
    ];
    $guruPermissions = collect($allPermissions)->filter(function ($p) use ($guruGroups) {
      $group = explode('.', $p)[0];
      return in_array($group, $guruGroups);
    })->merge($guruExtra)->unique()->values()->all();

    $guru = Role::create(['name' => 'guru']);
    $guru->givePermissionTo($guruPermissions);

    // Siswa permissions
    $siswaPermissions = [
      'material.view',
      'assignment.view',
      'assignment.submit',
      'exam.view',
      'exam.take',
      'announcement.view',
      'analytics.view',
    ];
    $siswa = Role::create(['name' => 'siswa']);
    $siswa->givePermissionTo($siswaPermissions);
  }
}
