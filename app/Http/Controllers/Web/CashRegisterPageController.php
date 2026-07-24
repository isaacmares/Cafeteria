<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CashRegisterPageController extends Controller
{
    public function index()
    {
        return Inertia::render('CashRegister/Index', [
            'tenant' => Auth::user()->tenant
        ]);
    }
}
