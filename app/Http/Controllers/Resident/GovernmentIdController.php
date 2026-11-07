<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\ResidentRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GovernmentIdController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user?->mustUploadGovernmentId()) {
            return redirect()->route('resident.dashboard');
        }

        return view('resident.government-id', [
            'user' => $user,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user?->mustUploadGovernmentId()) {
            return redirect()->route('resident.dashboard');
        }

        $validated = $request->validate([
            'government_id_type' => ['required', 'string', 'in:' . ResidentRegistrationService::GOVERNMENT_ID_TYPES],
            'government_id_proof' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'government_id_type.required' => 'Please select a government ID type.',
            'government_id_type.in' => 'The selected government ID type is invalid.',
            'government_id_proof.required' => 'Government ID upload is required.',
            'government_id_proof.mimes' => 'Government ID proof must be a JPG or PNG file.',
            'government_id_proof.max' => 'Government ID proof must not exceed 2MB.',
            'government_id_proof.uploaded' => 'Government ID upload failed. Please use a smaller image (max 2MB) and try again.',
        ]);

        $path = $request->file('government_id_proof')->store('id-proofs/government', 'public');

        $user->forceFill([
            'government_id_type' => $validated['government_id_type'],
            'government_id_path' => $path,
        ])->save();

        AuditService::log(
            'government_id_uploaded',
            $user,
            'Resident uploaded the government ID copy after in-person verification.'
        );

        return redirect()
            ->route('resident.dashboard')
            ->with('success', 'Government ID uploaded. You can now use barangay services.');
    }
}
