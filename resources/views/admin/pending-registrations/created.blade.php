@extends($layout ?? 'layouts.admin')

@section('title', 'Resident registered - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="mb-6">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Registration Management</h1>
        </div>
        @include('admin.pending-registrations.partials.tabs')
        <div class="mx-auto max-w-xl">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Resident registered</h1>
            <p class="mt-2 text-sm text-gray-600">
                Write this code down and give it to {{ $credentials['name'] ?? 'the resident' }}. It is shown only on this screen. The resident uses it once to create an email and password.
            </p>

            <dl class="mt-6 space-y-4 text-sm">
                <div>
                    <dt class="font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-gray-900">{{ $credentials['name'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Activation code</dt>
                    <dd class="mt-1 rounded-lg bg-gray-50 px-3 py-2 text-center font-mono text-2xl tracking-widest text-gray-900">{{ $credentials['code'] }}</dd>
                </div>
            </dl>

            <a href="{{ route($rp . '.pending-registrations.index', ['status' => 'registered']) }}"
               class="mt-6 inline-flex rounded-lg bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">
                Back to registrations
            </a>
        </div>
        </div>
    </div>
</section>
@endsection
