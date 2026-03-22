@php
    $cards = [
        [
            'key' => 'pwd',
            'label' => 'PWD',
            'claimed_field' => 'is_pwd',
            'status_field' => 'pwd_status',
            'proof_field' => 'pwd_proof_path',
            'accent' => 'blue',
            'hint' => 'Upload PWD ID or valid supporting document.',
        ],
        [
            'key' => 'senior',
            'label' => 'Senior Citizen',
            'claimed_field' => 'is_senior',
            'status_field' => 'senior_status',
            'proof_field' => 'senior_proof_path',
            'accent' => 'purple',
            'hint' => 'Upload Senior Citizen ID or valid supporting document.',
        ],
    ];

    $statusPills = [
        'verified' => 'bg-green-100 text-green-800',
        'pending' => 'bg-amber-100 text-amber-800',
        'rejected' => 'bg-red-100 text-red-800',
        'not_submitted' => 'bg-gray-100 text-gray-700',
        null => 'bg-gray-100 text-gray-700',
    ];
@endphp

<div class="space-y-4">
    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
        Claims are optional at registration. Submit or update proof documents here for barangay verification.
    </div>

    @foreach ($cards as $card)
        @php
            $claimed = (bool) data_get($user, $card['claimed_field']);
            $status = data_get($user, $card['status_field']);
            $proofPath = data_get($user, $card['proof_field']);
            $statusLabel = $status ? str_replace('_', ' ', $status) : 'not submitted';
            $pillClass = $statusPills[$status] ?? $statusPills[null];
        @endphp
        <div class="ui-surface-card p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">{{ $card['label'] }}</h2>
                    <p class="text-xs text-gray-500 mt-1">{{ $card['hint'] }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $claimed ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' }}">
                        {{ $claimed ? 'Claimed' : 'Not Claimed' }}
                    </span>
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $pillClass }}">
                        {{ ucfirst($statusLabel) }}
                    </span>
                </div>
            </div>

            @if ($proofPath)
                <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs">
                    <span class="text-gray-600">Current proof:</span>
                    <a href="{{ asset('storage/' . $proofPath) }}" target="_blank" class="font-medium text-blue-600 hover:text-blue-700">
                        View uploaded file
                    </a>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.verifications.update', ['type' => $card['key']]) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                @csrf
                <div class="flex items-center gap-6">
                    <label class="inline-flex items-center">
                        <input type="radio" name="is_claimed" value="yes"
                               @checked(old('is_claimed', $claimed ? 'yes' : 'no') === 'yes')
                               class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Claim this classification</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="is_claimed" value="no"
                               @checked(old('is_claimed', $claimed ? 'yes' : 'no') === 'no')
                               class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Remove claim</span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-wider text-gray-500 mb-1">Upload Proof (optional)</label>
                    <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-xs text-gray-500">Accepted formats: JPG, PNG, PDF (max 2MB). New upload resets status to pending.</p>
                    @error('proof')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="ui-focus-ring inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                        Save {{ $card['label'] }} Claim
                    </button>
                </div>
            </form>
        </div>
    @endforeach
</div>
