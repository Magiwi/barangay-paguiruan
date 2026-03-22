<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Services\AuditService;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsManagementController extends Controller
{
    public function index(): View
    {
        $this->ensureDefaultTemplates();

        $templates = SmsTemplate::query()
            ->orderByRaw("FIELD(`key`, 'certificate_released_pickup', 'permit_released_pickup')")
            ->orderBy('id')
            ->get();

        $logs = SmsLog::query()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.sms.index', compact('templates', 'logs'));
    }

    public function updateTemplate(Request $request, SmsTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $template->update([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditService::log('sms_template_updated', $template, "Updated SMS template: {$template->key}");

        return back()->with('success', 'SMS template updated successfully.');
    }

    public function sendTest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template_key' => ['required', 'string', 'exists:sms_templates,key'],
            'test_mobile' => ['required', 'string', 'regex:/^(\+63|0)?9[0-9]{9}$/'],
            'sample_name' => ['nullable', 'string', 'max:255'],
            'sample_request_type' => ['nullable', 'string', 'max:255'],
            'sample_reference_id' => ['nullable', 'string', 'max:50'],
            'sample_pickup_location' => ['nullable', 'string', 'max:255'],
        ], [
            'test_mobile.regex' => 'Enter a valid PH mobile number (e.g. +639171234567 or 09171234567).',
        ]);

        $template = SmsTemplate::query()->where('key', $validated['template_key'])->firstOrFail();

        $result = SmsService::sendTestMessage(
            (string) $validated['test_mobile'],
            $template->key,
            [
                'name' => $validated['sample_name'] ?: 'Juan Dela Cruz',
                'request_type' => $validated['sample_request_type'] ?: 'Barangay Clearance',
                'reference_id' => $validated['sample_reference_id'] ?: '12345',
                'pickup_location' => $validated['sample_pickup_location'] ?: 'Barangay Hall',
            ],
            $template->message
        );

        AuditService::log(
            'sms_test_sent',
            null,
            "Sent SMS test using template {$template->key} to {$validated['test_mobile']} ({$result['status']})"
        );

        if ($result['status'] !== 'sent') {
            return back()->with('error', 'Test SMS failed to send. Check SMS Logs below.');
        }

        return back()->with('success', 'Test SMS sent successfully.');
    }

    private function ensureDefaultTemplates(): void
    {
        SmsTemplate::query()->firstOrCreate(
            ['key' => 'certificate_released_pickup'],
            [
                'title' => 'Certificate Released (Pickup)',
                'message' => 'Magandang araw {name}! Ang iyong {request_type} request (#{reference_id}) ay RELEASED at ready for pickup sa {pickup_location}. Dalhin ang valid ID at claim stub. Salamat.',
                'is_active' => true,
            ]
        );

        SmsTemplate::query()->firstOrCreate(
            ['key' => 'permit_released_pickup'],
            [
                'title' => 'Permit Released (Pickup)',
                'message' => 'Magandang araw {name}! Ang iyong {request_type} request (#{reference_id}) ay RELEASED at ready for pickup sa {pickup_location}. Dalhin ang valid ID at claim stub. Salamat.',
                'is_active' => true,
            ]
        );
    }
}
