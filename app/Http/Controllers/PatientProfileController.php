<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePatientProfileRequest;
use App\Models\PatientProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PatientProfileController extends Controller
{
    public function edit(): View
    {
        $profile = PatientProfile::where('user_id', auth()->id())->first();

        return view('patient.profile.edit', compact('profile'));
    }

    public function update(UpdatePatientProfileRequest $request): RedirectResponse
    {
        PatientProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated()
        );

        return redirect()->route('patient.profile.edit')->with('status', 'Health profile saved.');
    }
}