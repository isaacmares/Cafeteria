<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardPageController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'tenant' => Auth::user()->tenant
        ]);
    }
}
