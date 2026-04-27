<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateRequest;
use App\Services\AuditService;
use App\Services\BarangayOfficialRosterService;
use App\Services\NotificationService;
use App\Services\SmsService;
use App\Support\OfficialsPdfSnapshot;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CertificateController extends Controller
{
    public function __construct(
        private readonly BarangayOfficialRosterService $officialRoster,
    ) {}

    public function index(Request $request)
    {
        $query = CertificateRequest::with(['user', 'releasedBy', 'reviewedBy'])->latest();

        // Status filter
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Certificate type filter
        if ($type = $request->get('certificate_type')) {
            $query->where('certificate_type', $type);
        }

        $requests = $query->paginate(15)->withQueryString();

        return view('admin.certificates.index', compact('requests'));
    }

    public function update(Request $request, CertificateRequest $certificate)
    {
        // Prevent updating released certificates
        if ($certificate->status === 'released') {
            return back()->with('error', 'Released certificates cannot be modified.');
        }

        $rules = [
            'status' => ['required', 'string', 'in:approved,rejected'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];

        // Remarks required when rejecting
        if ($request->input('status') === 'rejected') {
            $rules['remarks'] = ['required', 'string', 'max:1000'];
        }

        $validated = $request->validate($rules, [
            'remarks.required' => 'Please provide a reason for rejection.',
        ]);

        $certificate->update($validated);

        AuditService::log(
            'certificate_'.$validated['status'],
            $certificate,
            ucfirst($validated['status'])." certificate #{$certificate->id} ({$certificate->certificate_type})"
        );

        if ($certificate->user) {
            $status = $validated['status'];
            $type = $certificate->certificate_type;
            $title = $status === 'approved' ? 'Certificate Approved' : 'Certificate Rejected';
            $body = $status === 'approved'
                ? "Your {$type} request has been approved and is ready for pickup."
                : "Your {$type} request has been rejected.".($validated['remarks'] ? " Reason: {$validated['remarks']}" : '');
            NotificationService::notify($certificate->user, $title, $body, 'certificate', $certificate->id);
        }

        $message = $validated['status'] === 'approved'
            ? 'Certificate request approved.'
            : 'Certificate request rejected.';

        return back()->with('success', $message);
    }

    /**
     * Show pre-approval review/edit form (pending only).
     */
    public function editBeforeApproval(CertificateRequest $certificate)
    {
        $this->ensurePendingReviewable($certificate);
        $certificate->load('user.purokRelation');

        $isIndigency = $this->isIndigencyCertificate($certificate);
        $monthlyIncomeOptions = $this->monthlyIncomeOptions();

        return view('admin.certificates.review', compact('certificate', 'isIndigency', 'monthlyIncomeOptions'));
    }

    /**
     * Save pre-approval review details (pending only).
     */
    public function updateBeforeApproval(Request $request, CertificateRequest $certificate)
    {
        $this->ensurePendingReviewable($certificate);

        $isResidency = $this->isResidencyCertificate($certificate);
        $isIndigency = $this->isIndigencyCertificate($certificate);
        $validated = $request->validate([
            'certificate_name_override' => ['nullable', 'string', 'max:255'],
            'certificate_address_override' => ['nullable', 'string', 'max:255'],
            'certificate_issued_on' => ['required', 'date'],
            'purpose' => ['required', 'string', 'max:1000'],
            'residency_years_text' => $isResidency ? ['required', 'string', 'max:100'] : ['nullable', 'string', 'max:100'],
            'monthly_income' => $isIndigency
                ? ['required', Rule::in($this->monthlyIncomeOptions())]
                : ['nullable', 'string', 'max:255'],
        ]);

        if (! $isResidency) {
            unset($validated['residency_years_text']);
        }

        $extraFields = is_array($certificate->extra_fields) ? $certificate->extra_fields : [];
        if ($isIndigency) {
            $extraFields['monthly_income'] = (string) $validated['monthly_income'];
        }
        unset($validated['monthly_income']);

        $validated['extra_fields'] = $extraFields;
        $validated['reviewed_at'] = now();
        $validated['reviewed_by'] = Auth::id();

        $certificate->update($validated);

        AuditService::log(
            'certificate_preapproval_updated',
            $certificate,
            "Updated pending certificate #{$certificate->id} before decision"
        );

        return back()->with('success', 'Certificate details updated. You may now approve or reject.');
    }

    /**
     * Show the editable residency template form (approved requests only).
     */
    public function editResidencyTemplate(CertificateRequest $certificate)
    {
        $this->ensureResidencyApproved($certificate);

        $certificate->load('user.purokRelation');

        return view('admin.certificates.residency-template', compact('certificate'));
    }

    /**
     * Save admin overrides used by the residency template.
     */
    public function updateResidencyTemplate(Request $request, CertificateRequest $certificate)
    {
        $this->ensureResidencyApproved($certificate);

        $validated = $request->validate([
            'certificate_name_override' => ['nullable', 'string', 'max:255'],
            'certificate_address_override' => ['nullable', 'string', 'max:255'],
            'residency_years_text' => ['required', 'string', 'max:100'],
            'certificate_issued_on' => ['required', 'date'],
            'purpose' => ['required', 'string', 'max:1000'],
        ]);

        $certificate->update($validated);

        AuditService::log(
            'certificate_template_updated',
            $certificate,
            "Updated residency template fields for certificate request #{$certificate->id}"
        );

        return back()->with('success', 'Residency template details updated.');
    }

    /**
     * Print residency certificate template as PDF (approved only).
     */
    public function printResidencyTemplate(CertificateRequest $certificate)
    {
        $this->ensureResidencyPrintable($certificate);

        $certificate->load('user.purokRelation');

        $officialsPdf = $this->officialsPdfForCertificate($certificate);

        $pdf = Pdf::loadView('certificates.templates.residency', compact('certificate', 'officialsPdf'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('certificate_of_residency_'.$certificate->id.'.pdf');
    }

    public function printIndigencyTemplate(CertificateRequest $certificate)
    {
        $this->ensureIndigencyPrintable($certificate);

        $certificate->load('user.purokRelation');

        $officialsPdf = $this->officialsPdfForCertificate($certificate);

        $pdf = Pdf::loadView('certificates.templates.indigency', compact('certificate', 'officialsPdf'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('certificate_of_indigency_'.$certificate->id.'.pdf');
    }

    public function printClearanceTemplate(CertificateRequest $certificate)
    {
        $this->ensureClearancePrintable($certificate);

        $certificate->load('user.purokRelation');

        $officialsPdf = $this->officialsPdfForCertificate($certificate);

        $pdf = Pdf::loadView('certificates.templates.clearance', compact('certificate', 'officialsPdf'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('barangay_clearance_'.$certificate->id.'.pdf');
    }

    public function printBarangayCertificateTemplate(CertificateRequest $certificate)
    {
        $this->ensureBarangayCertificatePrintable($certificate);

        $certificate->load('user.purokRelation');

        $officialsPdf = $this->officialsPdfForCertificate($certificate);

        $pdf = Pdf::loadView('certificates.templates.barangay-certificate', compact('certificate', 'officialsPdf'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('barangay_certificate_'.$certificate->id.'.pdf');
    }

    /**
     * Mark an approved certificate as released.
     */
    public function release(CertificateRequest $certificate)
    {
        // Only approved certificates can be released
        if ($certificate->status !== 'approved') {
            return back()->with('error', 'Only approved certificates can be released.');
        }

        $payload = [
            'status' => 'released',
            'released_at' => now(),
            'released_by' => Auth::id(),
        ];

        if ($certificate->officials_snapshot === null) {
            $payload['officials_snapshot'] = OfficialsPdfSnapshot::fromPdfRosters(
                $this->officialRoster->pdfRosters()
            );
        }

        $certificate->update($payload);

        AuditService::log('certificate_released', $certificate, "Released certificate #{$certificate->id} ({$certificate->certificate_type})");

        if ($certificate->user) {
            NotificationService::notify(
                $certificate->user,
                'Certificate Released',
                "Your {$certificate->certificate_type} has been released. You may now claim it at the barangay hall.",
                'certificate',
                $certificate->id
            );

            SmsService::sendCertificatePickupNotice(
                $certificate->user,
                (string) $certificate->certificate_type,
                (int) $certificate->id
            );
        }

        return back()->with('success', 'Certificate marked as released.');
    }

    /**
     * @return array<string, mixed>
     */
    private function officialsPdfForCertificate(CertificateRequest $certificate): array
    {
        return OfficialsPdfSnapshot::forPrint(
            $certificate->officials_snapshot,
            $this->officialRoster->pdfRosters()
        );
    }

    private function ensureResidencyApproved(CertificateRequest $certificate): void
    {
        if (! $this->isResidencyCertificate($certificate)) {
            abort(404);
        }

        if ($certificate->status !== 'approved') {
            abort(403, 'Residency template is only available for approved requests.');
        }
    }

    private function ensureResidencyPrintable(CertificateRequest $certificate): void
    {
        if (! $this->isResidencyCertificate($certificate)) {
            abort(404);
        }

        if (! in_array($certificate->status, ['approved', 'released'], true)) {
            abort(403, 'Residency template is only available for approved or released requests.');
        }
    }

    private function isResidencyCertificate(CertificateRequest $certificate): bool
    {
        return str_contains(strtolower((string) $certificate->certificate_type), 'residency');
    }

    private function ensureIndigencyPrintable(CertificateRequest $certificate): void
    {
        if (! $this->isIndigencyCertificate($certificate)) {
            abort(404);
        }

        if (! in_array($certificate->status, ['approved', 'released'], true)) {
            abort(403, 'Indigency template is only available for approved or released requests.');
        }
    }

    private function isIndigencyCertificate(CertificateRequest $certificate): bool
    {
        return str_contains(strtolower((string) $certificate->certificate_type), 'indigency');
    }

    private function ensureClearancePrintable(CertificateRequest $certificate): void
    {
        if (! $this->isClearanceCertificate($certificate)) {
            abort(404);
        }

        if (! in_array($certificate->status, ['approved', 'released'], true)) {
            abort(403, 'Clearance template is only available for approved or released requests.');
        }
    }

    private function isClearanceCertificate(CertificateRequest $certificate): bool
    {
        return str_contains(strtolower((string) $certificate->certificate_type), 'clearance');
    }

    private function ensureBarangayCertificatePrintable(CertificateRequest $certificate): void
    {
        if (! $this->isBarangayCertificate($certificate)) {
            abort(404);
        }

        if (! in_array($certificate->status, ['approved', 'released'], true)) {
            abort(403, 'Barangay certificate template is only available for approved or released requests.');
        }
    }

    private function isBarangayCertificate(CertificateRequest $certificate): bool
    {
        return strtolower(trim((string) $certificate->certificate_type)) === 'barangay certificate';
    }

    private function ensurePendingReviewable(CertificateRequest $certificate): void
    {
        if ($certificate->status !== 'pending') {
            abort(403, 'Only pending certificate requests can be reviewed before approval.');
        }
    }

    private function monthlyIncomeOptions(): array
    {
        return [
            'Below ₱5,000',
            '₱5,000 – ₱9,999',
            '₱10,000 – ₱14,999',
            '₱15,000 – ₱19,999',
            '₱20,000 and above',
            'No Income',
        ];
    }
}
