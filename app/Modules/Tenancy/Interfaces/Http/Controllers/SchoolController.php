<?php

namespace App\Modules\Tenancy\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenancy\Application\ProvisionSchool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class SchoolController extends Controller
{
    public function store(Request $request, ProvisionSchool $provisionSchool): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'alpha_dash', 'max:100'], 'domain' => ['required', 'string', 'max:255', 'unique:domains,domain'], 'timezone' => ['nullable', 'timezone'], 'locale' => ['nullable', 'string', 'max:10'], 'admin_name' => ['required', 'string', 'max:255'], 'admin_email' => ['required', 'email', 'max:255'], 'admin_password' => ['required', 'string', 'min:12']]);
        $data['slug'] ??= Str::slug($data['name']).'-'.Str::lower(Str::random(6));
        $data['timezone'] ??= 'UTC';
        $data['locale'] ??= 'en';
        $school = $provisionSchool->handle($data);

        return response()->json(['data' => ['id' => $school->id, 'name' => $school->name, 'domain' => $data['domain']]], 201);
    }
}
