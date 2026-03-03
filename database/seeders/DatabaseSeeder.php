<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminSeeder::class,
            TeacherSeeder::class,
            ClassroomSeeder::class,
            StudentSeeder::class,
            SubjectSeeder::class,
            LearningMaterialSeeder::class,
            AssignmentSeeder::class,
            ExaminationSeeder::class,
            AnnouncementSeeder::class,
        ]);
    }
}
