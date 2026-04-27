@extends('layouts.admin')

@section('title', 'SMS Management - e-Governance Admin Panel')
@section('page_title', 'SMS Management')

@section('content')
<section class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">SMS Messaging Module</h1>
            <p class="text-sm text-gray-500">Manage SMS templates for pickup notices and test live delivery.</p>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
        @endif
        @if ($errors->any())
            <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="space-y-4 xl:col-span-2">
                @foreach ($templates as $template)
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 px-5 py-4">
                            <h2 class="text-sm font-semibold text-gray-900">{{ $template->title }}</h2>
                            <p class="mt-0.5 text-xs text-gray-500">Template Key: <code>{{ $template->key }}</code></p>
                        </div>
                        <form method="POST" action="{{ route('admin.sms.templates.update', $template) }}" class="space-y-4 p-5" data-sms-template-form>
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Template Title</label>
                                <input type="text" name="title" value="{{ old('title', $template->title) }}"
                                       data-template-title
                                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Message Body</label>
                                <textarea name="message" rows="4"
                                          data-template-message
                                          class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">{{ old('message', $template->message) }}</textarea>
                            </div>

                            <label class="inline-flex items-center gap-2">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active))
                                       data-template-toggle
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Enable this template</span>
                            </label>
                            <p class="hidden text-xs text-amber-700" data-template-lock-note>
                                Template is locked while enabled. Uncheck this option first to edit title and message.
                            </p>

                            <div class="flex items-center justify-end">
                                <button type="submit"
                                        class="ui-btn ui-btn-primary inline-flex gap-1.5 rounded-lg">
                                    Save Template
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900">Send Test SMS</h3>
                    <p class="mt-1 text-xs text-gray-500">Use sample values to verify template output and delivery.</p>

                    <form method="POST" action="{{ route('admin.sms.test-send') }}" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Template</label>
                            <select name="template_key" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                @foreach ($templates as $template)
                                    <option value="{{ $template->key }}">{{ $template->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Test Mobile Number</label>
                            <input type="text" name="test_mobile" value="{{ old('test_mobile') }}" placeholder="09171234567 or +639171234567"
                                   class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Sample Name</label>
                            <input type="text" name="sample_name" value="{{ old('sample_name', 'Juan Dela Cruz') }}"
                                   class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Sample Request Type</label>
                            <input type="text" name="sample_request_type" value="{{ old('sample_request_type', 'Barangay Clearance') }}"
                                   class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Reference ID</label>
                                <input type="text" name="sample_reference_id" value="{{ old('sample_reference_id', '12345') }}"
                                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Pickup Location</label>
                                <input type="text" name="sample_pickup_location" value="{{ old('sample_pickup_location', 'Barangay Hall') }}"
                                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                            Send Test SMS
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-900">SMS Logs</h2>
                <p class="mt-0.5 text-xs text-gray-500">Latest SMS sending activity from templates and release events.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Recipient</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Template</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Message</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-600">
                                {{ optional($log->created_at)->format('M d, Y h:i A') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-700">
                                <div>{{ $log->mobile }}</div>
                                <div class="text-gray-500">{{ $log->user?->full_name ?? 'N/A' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-700">{{ $log->template_key ?? 'custom' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs">
                                <span class="inline-flex rounded-full px-2 py-0.5 font-medium {{ $log->status === 'sent' ? 'bg-emerald-100 text-emerald-700' : ($log->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ strtoupper($log->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ \Illuminate\Support\Str::limit($log->message, 100) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No SMS logs yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="border-t border-gray-100 px-5 py-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</section>

<script>
(function () {
    var forms = document.querySelectorAll('[data-sms-template-form]');

    forms.forEach(function (form) {
        var toggle = form.querySelector('[data-template-toggle]');
        var title = form.querySelector('[data-template-title]');
        var message = form.querySelector('[data-template-message]');
        var lockNote = form.querySelector('[data-template-lock-note]');
        if (!toggle || !title || !message) return;

        function applyLockState() {
            var locked = toggle.checked;
            title.readOnly = locked;
            message.readOnly = locked;
            title.classList.toggle('bg-gray-100', locked);
            title.classList.toggle('cursor-not-allowed', locked);
            message.classList.toggle('bg-gray-100', locked);
            message.classList.toggle('cursor-not-allowed', locked);
            if (lockNote) {
                lockNote.classList.toggle('hidden', !locked);
            }
        }

        toggle.addEventListener('change', applyLockState);
        applyLockState();
    });
})();
</script>
@endsection
