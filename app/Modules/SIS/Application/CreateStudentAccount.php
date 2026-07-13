<?php

namespace App\Modules\SIS\Application;

use App\Models\User;
use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class CreateStudentAccount
{
    /** @param array{email:string,password:string} $data */
    public function handle(Student $student, array $data): Student
    {
        abort_if($student->user_id, 422, 'This student already has an account.');

        return DB::transaction(function () use ($student, $data): Student {
            $user = User::query()->create(['name' => trim("{$student->first_name} {$student->last_name}"), 'email' => $data['email'], 'password' => Hash::make($data['password'])]);
            $user->assignRole('student');
            $student->update(['user_id' => $user->id]);

            return $student->refresh();
        });
    }
}
