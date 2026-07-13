<?php

use Illuminate\Support\Facades\Route;

// The admin/teacher SPA owns client-side routing; every non-API path returns
// the same shell so a deep link or refresh (e.g. /students) still boots React.
Route::view('/{any?}', 'dashboard')->where('any', '^(?!api).*$');
