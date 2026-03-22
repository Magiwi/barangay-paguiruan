<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Blotter;
use App\Models\BlotterRequest;
use App\Models\BlotterRevision;
use App\Models\User;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BlotterController extends Controller
{
    private array $viewData = [
        'layout' => 'layouts.staff',
        'routePrefix' => 'staff',
    ];

    public function __construct()
    {
        if (! auth()->check() || ! auth()->user()->canAccess('blotter')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    public function index(Request $request): View
    {
        $query = Blotter::with('uploadedBy')->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('blotter_number', 'like', "%{$search}%")
                  ->orWhere('complainant_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $blotters = $query->paginate(15)->withQueryString();

        $stats = Blotter::query()
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when status = 'active' then 1 else 0 end) as active_count")
            ->selectRaw("sum(case when status = 'archived' then 1 else 0 end) as archived_count")
            ->first();

        $retentionDays = (int) env('BLOTTER_RETENTION_DAYS', 365);

        return view('admin.blotters.index', array_merge(compact('blotters', 'stats', 'retentionDays'), $this->viewData));
    }

    public function create(): View
    {
        return view('admin.blotters.create', $this->viewData);
    }

    public function edit(Blotter $blotter): View
    {
        $revisions = $blotter->revisions()
            ->with('changedBy')
            ->latest()
            ->take(25)
            ->get();

        return view('admin.blotters.edit', array_merge(compact('blotter', 'revisions'), $this->viewData));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'complainant_name' => ['nullable', 'string', 'max:255'],
            'complainant_first_name' => ['required_without:complainant_name', 'nullable', 'string', 'max:100'],
            'complainant_middle_name' => ['nullable', 'string', 'max:100'],
            'complainant_last_name' => ['required_without:complainant_name', 'nullable', 'string', 'max:100'],
            'complainant_age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'complainant_contact' => ['nullable', 'regex:/^09[0-9]{9}$/'],
            'complainant_address' => ['nullable', 'string', 'max:255'],
            'respondent_first_name' => ['nullable', 'string', 'max:100'],
            'respondent_middle_name' => ['nullable', 'string', 'max:100'],
            'respondent_last_name' => ['nullable', 'string', 'max:100'],
            'respondent_residence' => ['nullable', 'string', 'max:255'],
            'witness_name' => ['nullable', 'string', 'max:255'],
            'witness_contact' => ['nullable', 'regex:/^09[0-9]{9}$/'],
            'scheduled_hearing_date' => ['nullable', 'date'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'handwritten_salaysay' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remarks' => ['nullable', 'string', 'max:3000'],
        ], [
            'complainant_contact.regex' => 'Complainant contact must start with 09 and contain 11 digits.',
            'witness_contact.regex' => 'Witness contact must start with 09 and contain 11 digits.',
            'handwritten_salaysay.required' => 'Handwritten Sinumpaang Salaysay image is required.',
            'handwritten_salaysay.mimes' => 'Handwritten Salaysay must be JPG, JPEG, PNG, or WEBP.',
            'handwritten_salaysay.max' => 'Handwritten Salaysay image must not exceed 10MB.',
            'file.mimes' => 'Only PDF, JPG, JPEG, PNG, and WEBP files are allowed.',
            'file.max' => 'File must not exceed 10MB.',
        ]);

        $complainantName = trim((string) ($validated['complainant_name'] ?? ''));
        if ($complainantName === '') {
            $complainantName = trim(implode(' ', array_filter([
                $validated['complainant_first_name'] ?? null,
                $validated['complainant_middle_name'] ?? null,
                $validated['complainant_last_name'] ?? null,
            ])));
        }

        $respondentName = trim(implode(' ', array_filter([
            $validated['respondent_first_name'] ?? null,
            $validated['respondent_middle_name'] ?? null,
            $validated['respondent_last_name'] ?? null,
        ])));

        $detailLines = [];
        if (! empty($validated['complainant_age'])) {
            $detailLines[] = 'Complainant Age: ' . $validated['complainant_age'];
        }
        if (! empty($validated['complainant_contact'])) {
            $detailLines[] = 'Complainant Contact: ' . $validated['complainant_contact'];
        }
        if (! empty($validated['complainant_address'])) {
            $detailLines[] = 'Complainant Address: ' . $validated['complainant_address'];
        }
        if ($respondentName !== '') {
            $detailLines[] = 'Respondent Name: ' . $respondentName;
        }
        if (! empty($validated['respondent_residence'])) {
            $detailLines[] = 'Respondent Residence: ' . $validated['respondent_residence'];
        }
        if (! empty($validated['witness_name'])) {
            $detailLines[] = 'Witness: ' . $validated['witness_name'];
        }
        if (! empty($validated['witness_contact'])) {
            $detailLines[] = 'Witness Contact: ' . $validated['witness_contact'];
        }
        if (! empty($validated['scheduled_hearing_date'])) {
            $detailLines[] = 'Scheduled Hearing Date: ' . $validated['scheduled_hearing_date'];
        }

        $remarks = trim((string) ($validated['remarks'] ?? ''));
        if ($detailLines !== []) {
            $structured = "Additional Blotter Details:\n- " . implode("\n- ", $detailLines);
            $remarks = $remarks !== '' ? $remarks . "\n\n" . $structured : $structured;
        }
        $remarks = mb_substr($remarks, 0, 5000);

        $blotter = DB::transaction(function () use ($validated, $request, $complainantName, $remarks) {
            $filePath = $request->hasFile('file')
                ? $request->file('file')->store('blotters', 'local')
                : null;
            $handwrittenSalaysayPath = $request->file('handwritten_salaysay')->store('blotters/handwritten-salaysay', 'local');

            $blotter = new Blotter([
                'complainant_name' => $complainantName,
                'complainant_user_id' => $request->input('complainant_user_id') ?: null,
                'incident_date' => $validated['incident_date'],
                'file_path' => $filePath,
                'handwritten_salaysay_path' => $handwrittenSalaysayPath,
                'remarks' => $remarks !== '' ? $remarks : null,
            ]);

            $blotter->blotter_number = Blotter::generateBlotterNumber();
            $blotter->uploaded_by = $request->user()->id;
            $blotter->status = Blotter::STATUS_ACTIVE;
            $blotter->save();

            return $blotter;
        });

        AuditService::log('blotter_uploaded', $blotter, "Uploaded blotter {$blotter->blotter_number}");

        return redirect()->route('staff.blotters.index')
            ->with('success', "Blotter {$blotter->blotter_number} uploaded successfully.");
    }

    public function update(Request $request, Blotter $blotter): RedirectResponse
    {
        if ($blotter->trashed()) {
            return back()->withErrors(['blotter' => 'Archived blotter records cannot be edited.']);
        }

        $validated = $request->validate([
            'complainant_name' => ['required', 'string', 'max:255'],
            'complainant_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'remove_file' => ['nullable', 'boolean'],
            'handwritten_salaysay' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'correction_note' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $beforeData = [
            'complainant_name' => (string) $blotter->complainant_name,
            'complainant_user_id' => $blotter->complainant_user_id ? (int) $blotter->complainant_user_id : null,
            'incident_date' => optional($blotter->incident_date)->toDateString(),
            'remarks' => (string) ($blotter->remarks ?? ''),
            'file_path' => (string) ($blotter->file_path ?? ''),
            'handwritten_salaysay_path' => (string) ($blotter->handwritten_salaysay_path ?? ''),
        ];

        $blotter->complainant_name = trim((string) $validated['complainant_name']);
        $blotter->complainant_user_id = $validated['complainant_user_id'] ?? null;
        $blotter->incident_date = $validated['incident_date'];
        $blotter->remarks = trim((string) ($validated['remarks'] ?? '')) ?: null;

        if ($request->hasFile('file')) {
            if ($blotter->file_path && Storage::disk('local')->exists($blotter->file_path)) {
                Storage::disk('local')->delete($blotter->file_path);
            }
            $blotter->file_path = $request->file('file')->store('blotters', 'local');
        } elseif ((bool) ($validated['remove_file'] ?? false)) {
            if ($blotter->file_path && Storage::disk('local')->exists($blotter->file_path)) {
                Storage::disk('local')->delete($blotter->file_path);
            }
            $blotter->file_path = null;
        }

        if ($request->hasFile('handwritten_salaysay')) {
            if ($blotter->handwritten_salaysay_path && Storage::disk('local')->exists($blotter->handwritten_salaysay_path)) {
                Storage::disk('local')->delete($blotter->handwritten_salaysay_path);
            }
            $blotter->handwritten_salaysay_path = $request->file('handwritten_salaysay')->store('blotters/handwritten-salaysay', 'local');
        }

        if (! $blotter->handwritten_salaysay_path) {
            return back()
                ->withErrors(['handwritten_salaysay' => 'Handwritten Sinumpaang Salaysay image is required.'])
                ->withInput();
        }

        $afterData = [
            'complainant_name' => (string) $blotter->complainant_name,
            'complainant_user_id' => $blotter->complainant_user_id ? (int) $blotter->complainant_user_id : null,
            'incident_date' => optional($blotter->incident_date)->toDateString(),
            'remarks' => (string) ($blotter->remarks ?? ''),
            'file_path' => (string) ($blotter->file_path ?? ''),
            'handwritten_salaysay_path' => (string) ($blotter->handwritten_salaysay_path ?? ''),
        ];

        $changedFields = [];
        foreach ($beforeData as $field => $value) {
            if (($afterData[$field] ?? null) !== $value) {
                $changedFields[] = $field;
            }
        }

        if ($changedFields === []) {
            return redirect()
                ->route('staff.blotters.edit', $blotter)
                ->with('success', 'No changes detected. Blotter record remains unchanged.');
        }

        DB::transaction(function () use ($blotter, $validated, $beforeData, $afterData, $changedFields): void {
            $blotter->save();

            BlotterRevision::create([
                'blotter_id' => $blotter->id,
                'changed_by' => auth()->id(),
                'action' => 'updated',
                'change_note' => $validated['correction_note'],
                'changed_fields' => $changedFields,
                'before_data' => $beforeData,
                'after_data' => $afterData,
            ]);
        });

        AuditService::log(
            'blotter_updated',
            $blotter,
            "Updated blotter {$blotter->blotter_number}. Fields: " . implode(', ', $changedFields)
        );

        return redirect()
            ->route('staff.blotters.edit', $blotter)
            ->with('success', "Blotter {$blotter->blotter_number} updated successfully.");
    }

    public function download(Blotter $blotter): StreamedResponse
    {
        if (! $blotter->file_path || ! Storage::disk('local')->exists($blotter->file_path)) {
            abort(404, 'File not found.');
        }

        $ext = pathinfo($blotter->file_path, PATHINFO_EXTENSION);
        $filename = "{$blotter->blotter_number}.{$ext}";

        return Storage::disk('local')->download($blotter->file_path, $filename);
    }

    /**
     * Preview specific blotter evidence inline (for quick-view modal).
     */
    public function previewEvidence(Blotter $blotter, string $type): \Illuminate\Http\Response
    {
        $path = match ($type) {
            'handwritten' => $blotter->handwritten_salaysay_path,
            'evidence' => $blotter->file_path,
            default => null,
        };

        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404, 'Evidence file not found.');
        }

        $disk = Storage::disk('local');
        $mime = $disk->mimeType($path) ?: 'application/octet-stream';

        return response($disk->get($path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function requests(Request $request): View
    {
        $query = BlotterRequest::with('blotter', 'user', 'processedBy')->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                  ->orWhereHas('blotter', fn ($b) => $b->where('blotter_number', 'like', "%{$search}%"));
            });
        }

        $blotterRequests = $query->paginate(10)->withQueryString();

        $stats = BlotterRequest::query()
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when status = 'pending' then 1 else 0 end) as pending_count")
            ->selectRaw("sum(case when status = 'approved' then 1 else 0 end) as approved_count")
            ->selectRaw("sum(case when status = 'rejected' then 1 else 0 end) as rejected_count")
            ->selectRaw("sum(case when status = 'released' then 1 else 0 end) as released_count")
            ->first();

        $rejectionReasonOptions = BlotterRequest::rejectionReasonOptions();

        return view('admin.blotter-requests.index', array_merge(compact('blotterRequests', 'stats', 'rejectionReasonOptions'), $this->viewData));
    }

    public function updateRequestStatus(Request $request, BlotterRequest $blotterRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:released'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! $blotterRequest->canTransitionTo($validated['status'])) {
            abort(422, "Cannot transition from '{$blotterRequest->status}' to '{$validated['status']}'.");
        }

        $blotterRequest->forceFill([
            'status' => $validated['status'],
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        if (! empty($validated['remarks'])) {
            $blotterRequest->remarks = $validated['remarks'];
        }

        $blotterRequest->save();

        AuditService::log('blotter_request_released', $blotterRequest, "Released blotter request #{$blotterRequest->id}");

        if ($blotterRequest->user) {
            NotificationService::notify(
                $blotterRequest->user,
                'Blotter Request Released',
                'Your blotter record has been released. You may now claim it at the barangay hall.',
                'blotter',
                $blotterRequest->id
            );
        }

        return redirect()->route('staff.blotter-requests.index')
            ->with('success', 'Blotter request released successfully.');
    }
}
