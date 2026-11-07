@extends('layouts.resident')

@section('title', 'Upload government ID - e-Governance')

@section('content')
<section class="mx-auto max-w-xl px-4 py-8">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold tracking-tight text-gray-800">Upload your government ID</h1>
        <p class="mt-2 text-sm text-gray-600">
            The barangay already checked your ID at the office. Upload a JPG or PNG copy before you can use certificates, permits, and other services.
        </p>

        @if (session('success'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('resident.government-id.update') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="government_id_type" class="block text-sm font-medium text-gray-700 mb-1">
                    Government ID Type <span class="text-red-600">*</span>
                </label>
                <select name="government_id_type" id="government_id_type" required
                        class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm">
                    <option value="">Select ID type</option>
                    @foreach ([
                        'national_id' => 'National ID',
                        'passport' => 'Passport',
                        'drivers_license' => "Driver's License",
                        'umid' => 'UMID',
                        'philhealth' => 'PhilHealth ID',
                        'postal_id' => 'Postal ID',
                        'voters_id' => "Voter's ID",
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('government_id_type', $user->government_id_type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="government_id_proof" class="block text-sm font-medium text-gray-700 mb-1">
                    Government ID Upload <span class="text-red-600">*</span>
                </label>
                <input type="file" name="government_id_proof" id="government_id_proof" required accept=".jpg,.jpeg,.png"
                       class="block w-full text-sm text-gray-500">
                <p class="mt-1 text-xs text-gray-500">JPG or PNG. Max file size: 2MB.</p>
            </div>
            <button type="submit" class="rounded-lg bg-green-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-800">
                Upload ID
            </button>
        </form>
    </div>
</section>
@endsection
