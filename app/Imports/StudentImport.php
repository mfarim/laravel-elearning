<?php

namespace App\Imports;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class StudentImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
  use SkipsFailures;

  public int $importedCount = 0;

  public function model(array $row)
  {
    $classroom = Classroom::where('name', $row['kelas'])->first();

    $user = User::create([
      'name' => $row['nama'],
      'email' => $row['email'],
      'password' => Hash::make($row['password'] ?? 'password'),
      'is_active' => true,
    ]);
    $user->assignRole('siswa');

    $this->importedCount++;

    return new Student([
      'user_id' => $user->id,
      'classroom_id' => $classroom?->id,
      'nis' => $row['nis'],
      'nisn' => $row['nisn'] ?? null,
      'gender' => $row['gender'] ?? 'L',
      'birth_date' => isset($row['tanggal_lahir']) ? \Carbon\Carbon::parse($row['tanggal_lahir']) : null,
      'address' => $row['alamat'] ?? null,
    ]);
  }

  public function rules(): array
  {
    return [
      'nama' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email',
      'nis' => 'required|string|unique:students,nis',
      'kelas' => 'required|string',
    ];
  }
}
