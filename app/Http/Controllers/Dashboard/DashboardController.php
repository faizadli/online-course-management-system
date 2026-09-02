<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function student(): View
    {
        return view('dashboard.student', ['role' => 'student']);
    }

    public function instructor(): View
    {
        return view('dashboard.instructor', ['role' => 'instructor']);
    }
}
