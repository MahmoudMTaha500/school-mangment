<?php

namespace App\Modules\SIS\Application;

use App\Modules\SIS\Domain\Models\ParentProfile;
use Illuminate\Support\Facades\DB;

final class ManageParent
{
    public function update(ParentProfile $parent, array $data): ParentProfile
    {
        return DB::transaction(function () use ($parent, $data): ParentProfile {
            $parent->user->update(array_filter(['name' => $data['name'] ?? null, 'email' => $data['email'] ?? null], fn ($value) => $value !== null));
            if (isset($data['status'])) {
                $parent->update(['status' => $data['status'], 'archived_at' => $data['status'] === 'archived' ? now() : null]);
            }

            return $parent->refresh()->load(['user', 'students']);
        });
    }

    public function archive(ParentProfile $parent): void
    {
        $parent->update(['status' => 'archived', 'archived_at' => now()]);
    }
}
