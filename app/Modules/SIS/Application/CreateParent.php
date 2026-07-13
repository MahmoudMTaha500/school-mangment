<?php

namespace App\Modules\SIS\Application;

use App\Models\User;
use App\Modules\SIS\Domain\Models\ParentProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class CreateParent
{
    /** @param array{name:string,email:string,password:string} $data */
    public function handle(array $data): ParentProfile
    {
        return DB::transaction(function () use ($data): ParentProfile {
            $user = User::query()->create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password'])]);
            $user->assignRole('parent');

            return ParentProfile::query()->create(['user_id' => $user->id]);
        });
    }
}
