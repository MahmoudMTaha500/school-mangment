<?php

namespace App\Modules\Staff\Application;

use App\Modules\Staff\Domain\Models\Teacher;
use Illuminate\Support\Facades\DB;

final class ManageTeacher
{
    public function update(Teacher $teacher, array $data): Teacher
    {
        return DB::transaction(function () use ($teacher, $data): Teacher {
            $teacher->user->update(array_filter(['name' => $data['name'] ?? null, 'email' => $data['email'] ?? null], fn ($value) => $value !== null));
            $profile = array_filter(['staff_no' => $data['staff_no'] ?? null, 'status' => $data['status'] ?? null], fn ($value) => $value !== null);
            if (isset($data['status'])) {
                $profile['archived_at'] = $data['status'] === 'archived' ? now() : null;
            }
            $teacher->update($profile);

            return $teacher->refresh()->load('user');
        });
    }

    public function archive(Teacher $teacher): void
    {
        $teacher->update(['status' => 'archived', 'archived_at' => now()]);
    }
}
