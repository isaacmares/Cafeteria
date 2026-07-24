<?php

namespace App\Http\Controllers\Onboarding;

use App\Actions\Company\CreateCompanyAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyOnboardingController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Onboarding/Company');
    }

    public function store(Request $request, CreateCompanyAction $createCompany): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'rfc' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $createCompany->execute(
            $request->user(),
            $validated,
            $request->file('logo')
        );

        return redirect()->route('dashboard');
    }
}
