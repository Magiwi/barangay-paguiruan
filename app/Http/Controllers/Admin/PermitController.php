<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permit;
use App\Services\NotificationService;
use App\Services\SmsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PermitController extends Controller
{
    public function index(Request $request)
    {
        $query = Permit::with('applicant')->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $permits = $query->paginate(15)->withQueryString();

        return view('admin.permits.index', compact('permits'));
    }

    public function approve(Request $request, Permit $permit)
    {
        if ($permit->status !== 'pending') {
            return back()->with('error', 'Only pending permits can be approved.');
        }

        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $permit->update([
            'status' => 'approved',
            'remarks' => $validated['remarks'] ?? $permit->remarks,
        ]);

        if ($permit->applicant) {
            NotificationService::notify(
                $permit->applicant,
                'Permit Approved',
                "Your {$permit->permit_type} permit application has been approved.",
                'permit',
                $permit->id
            );
        }

        return back()->with('success', 'Permit approved.');
    }

    public function reject(Request $request, Permit $permit)
    {
        if ($permit->status !== 'pending') {
            return back()->with('error', 'Only pending permits can be rejected.');
        }

        $validated = $request->validate([
            'remarks' => ['required', 'string', 'max:1000'],
        ], [
            'remarks.required' => 'Please provide a reason for rejection.',
        ]);

        $permit->update([
            'status' => 'rejected',
            'remarks' => $validated['remarks'],
        ]);

        if ($permit->applicant) {
            NotificationService::notify(
                $permit->applicant,
                'Permit Rejected',
                "Your {$permit->permit_type} permit application has been rejected. Reason: {$validated['remarks']}",
                'permit',
                $permit->id
            );
        }

        return back()->with('success', 'Permit rejected.');
    }

    public function release(Permit $permit)
    {
        if ($permit->status !== 'approved') {
            return back()->with('error', 'Only approved permits can be released.');
        }

        $permit->update([
            'status' => 'released',
            'released_at' => now(),
            'released_by' => Auth::id(),
        ]);

        if ($permit->applicant) {
            NotificationService::notify(
                $permit->applicant,
                'Permit Released',
                "Your {$permit->permit_type} permit has been released. You may now claim it at the barangay hall.",
                'permit',
                $permit->id
            );

            SmsService::sendPermitPickupNotice(
                $permit->applicant,
                (string) $permit->permit_type,
                (int) $permit->id
            );
        }

        return back()->with('success', 'Permit marked as released.');
    }

    public function printBusinessTemplate(Permit $permit)
    {
        $this->ensureBusinessPrintable($permit);

        $permit->load(['applicant.purokRelation', 'releasedBy']);

        $pdf = Pdf::loadView('permits.templates.business', compact('permit'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('business_permit_' . $permit->id . '.pdf');
    }

    public function printEventTemplate(Permit $permit)
    {
        $this->ensureEventPrintable($permit);

        $permit->load(['applicant.purokRelation', 'releasedBy']);

        $pdf = Pdf::loadView('permits.templates.event', compact('permit'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('event_permit_' . $permit->id . '.pdf');
    }

    public function printBuildingTemplate(Permit $permit)
    {
        $this->ensureBuildingPrintable($permit);

        $permit->load(['applicant.purokRelation', 'releasedBy']);

        $pdf = Pdf::loadView('permits.templates.building', compact('permit'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('building_permit_' . $permit->id . '.pdf');
    }

    /**
     * Securely serve the uploaded permit document.
     */
    public function viewDocument(Permit $permit)
    {
        if (! $permit->document_path) {
            abort(404, 'No document attached to this permit.');
        }

        if (! Storage::disk('public')->exists($permit->document_path)) {
            abort(404, 'Document file not found.');
        }

        return response()->file(
            Storage::disk('public')->path($permit->document_path)
        );
    }

    private function ensureBusinessPrintable(Permit $permit): void
    {
        if (! $this->isBusinessPermit($permit)) {
            abort(404);
        }

        if (! in_array($permit->status, ['approved', 'released'], true)) {
            abort(403, 'Business permit template is only available for approved or released applications.');
        }
    }

    private function isBusinessPermit(Permit $permit): bool
    {
        return strtolower(trim((string) $permit->permit_type)) === 'business permit';
    }

    private function ensureEventPrintable(Permit $permit): void
    {
        if (! $this->isEventPermit($permit)) {
            abort(404);
        }

        if (! in_array($permit->status, ['approved', 'released'], true)) {
            abort(403, 'Event permit template is only available for approved or released applications.');
        }
    }

    private function isEventPermit(Permit $permit): bool
    {
        return strtolower(trim((string) $permit->permit_type)) === 'event permit';
    }

    private function ensureBuildingPrintable(Permit $permit): void
    {
        if (! $this->isBuildingPermit($permit)) {
            abort(404);
        }

        if (! in_array($permit->status, ['approved', 'released'], true)) {
            abort(403, 'Building permit template is only available for approved or released applications.');
        }
    }

    private function isBuildingPermit(Permit $permit): bool
    {
        return strtolower(trim((string) $permit->permit_type)) === 'building permit';
    }
}
