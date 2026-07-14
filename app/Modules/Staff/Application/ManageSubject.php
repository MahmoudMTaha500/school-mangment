<?php

namespace App\Modules\Staff\Application;

use App\Modules\Staff\Domain\Models\Subject;

final class ManageSubject
{
    public function update(Subject $subject, array $data): Subject
    {
        if (isset($data['status'])) {
            $data['archived_at'] = $data['status'] === 'archived' ? now() : null;
        }
        $subject->update($data);

        return $subject->refresh();
    }

    public function archive(Subject $subject): void
    {
        $subject->update(['status' => 'archived', 'archived_at' => now()]);
    }
}
