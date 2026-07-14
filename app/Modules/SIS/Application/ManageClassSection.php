<?php

namespace App\Modules\SIS\Application;

use App\Modules\SIS\Domain\Models\ClassSection;

final class ManageClassSection
{
    public function update(ClassSection $classSection, array $data): ClassSection
    {
        if (isset($data['status'])) {
            $data['archived_at'] = $data['status'] === 'archived' ? now() : null;
        }
        $classSection->update($data);

        return $classSection->refresh()->load('academicYear')->loadCount('students');
    }

    public function archive(ClassSection $classSection): void
    {
        $classSection->update(['status' => 'archived', 'archived_at' => now()]);
    }
}
