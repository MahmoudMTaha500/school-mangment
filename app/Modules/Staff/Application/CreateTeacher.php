<?php

namespace App\Modules\Staff\Application;

use App\Models\User;
use App\Modules\Staff\Domain\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class CreateTeacher
{
    /** @param array{name:string,email:string,password:string,staff_no:string} $data */
    public function handle(array $data): Teacher
    {
        return DB::transaction(function () use ($data): Teacher {
            $user = User::query()->create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password'])]);
            $user->assignRole('teacher');

            return Teacher::query()->create(['user_id' => $user->id, 'staff_no' => $data['staff_no']]);
        });
    }
}
