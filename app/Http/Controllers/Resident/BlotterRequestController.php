<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Blotter;
use App\Models\BlotterRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlotterRequestController extends Controller
{
    // ──────────────────────────────────────────────────
    // INDEX — list authenticated resident's requests
    // ──────────────────────────────────────────────────
    public function index(): View
    {
        $requests = BlotterRequest::with('blotter', 'processedBy')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('resident.blotter-requests.index', compact('requests'));
    }

    // ──────────────────────────────────────────────────
    // CREATE — show form with active blotters dropdown
    // ──────────────────────────────────────────────────
    public function create(): View
    {
        $userId = auth()->id();

        $blotters = Blotter::query()
            ->where('status', Blotter::STATUS_ACTIVE)
            ->whereNull('deleted_at')
            ->whereNotNull('complainant_user_id')
            ->where('complainant_user_id', $userId)
            ->orderByDesc('incident_date')
            ->get(['id', 'blotter_number', 'complainant_name', 'incident_date']);

        return view('resident.blotter-requests.create', compact('blotters'));
    }

    // ──────────────────────────────────────────────────
    // STORE — validate and persist new request
    // ──────────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'blotter_id' => [
                'required',
                'integer',
                Rule::exists('blotters', 'id')->where(function ($query): void {
                    $query->whereNull('deleted_at')->where('status', Blotter::STATUS_ACTIVE);
                }),
            ],
            'purpose' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'blotter_id.required' => 'Please select a blotter record.',
            'blotter_id.exists' => 'The selected blotter record is not available for request.',
            'purpose.min' => 'Purpose must be at least 10 characters.',
        ]);

        $blotter = Blotter::findOrFail($validated['blotter_id']);

        // Privacy guard: resident can only request their own complainant-linked blotter.
        if ((int) $blotter->complainant_user_id !== (int) auth()->id()) {
            return back()->withErrors([
                'blotter_id' => 'You are not authorized to request this blotter record.',
            ])->withInput();
        }

        // Prevent duplicate open requests for the same blotter.
        $hasDuplicateOpenRequest = BlotterRequest::query()
            ->where('user_id', auth()->id())
            ->where('blotter_id', $validated['blotter_id'])
            ->whereIn('status', [
                BlotterRequest::STATUS_PENDING,
                BlotterRequest::STATUS_APPROVED,
            ])
            ->exists();

        if ($hasDuplicateOpenRequest) {
            return back()->withErrors([
                'blotter_id' => 'You already have an active request for this blotter record.',
            ])->withInput();
        }

        $blotterRequest = new BlotterRequest([
            'blotter_id' => $validated['blotter_id'],
            'purpose' => $validated['purpose'],
        ]);

        // Set guarded fields explicitly then persist in a single INSERT
        $blotterRequest->user_id = auth()->id();
        $blotterRequest->status = BlotterRequest::STATUS_PENDING;
        $blotterRequest->save();

        NotificationService::notify(
            $request->user(),
            'Blotter Request Submitted',
            'Your blotter record request has been submitted and is pending review.',
            'blotter',
            $blotterRequest->id
        );

        return redirect()->route('resident.blotter-requests.index')
            ->with('success', 'Blotter request submitted successfully.');
    }
}
