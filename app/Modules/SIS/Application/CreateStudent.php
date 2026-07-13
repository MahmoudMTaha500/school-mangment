<?php

namespace App\Modules\SIS\Application;

use App\Modules\SIS\Domain\Models\Student;

final class CreateStudent
{
    /** @param array{code:string,first_name:string,last_name:string,dob?:string,class_section_id?:int,enrollment_status?:string} $data */
    public function handle(array $data): Student
    {
        return Student::query()->create($data);
    }
}
