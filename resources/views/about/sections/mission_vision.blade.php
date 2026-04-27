<section class="bg-white py-16 px-6">
    <div class="mx-auto max-w-7xl">
        <div class="text-center mb-14 fade-in">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">{{ $data['kicker'] ?? '' }}</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $data['heading'] ?? '' }}</h2>
        </div>
        <div class="mx-auto grid max-w-5xl grid-cols-1 gap-8 md:grid-cols-2">
            <div class="fade-in fade-delay-1 group rounded-2xl border-l-4 border-blue-700 bg-white p-8 shadow-md ring-1 ring-gray-100 hover:shadow-xl transition-all duration-300">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-700">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                    </svg>
                </div>
                <h3 class="mt-6 text-lg font-semibold tracking-tight text-gray-900">{{ $data['mission_title'] ?? '' }}</h3>
                <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                    {{ $data['mission_body'] ?? '' }}
                </p>
            </div>
            <div class="fade-in fade-delay-2 group rounded-2xl border-l-4 border-green-600 bg-white p-8 shadow-md ring-1 ring-gray-100 hover:shadow-xl transition-all duration-300">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-600">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="mt-6 text-lg font-semibold tracking-tight text-gray-900">{{ $data['vision_title'] ?? '' }}</h3>
                <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                    {{ $data['vision_body'] ?? '' }}
                </p>
            </div>
        </div>
    </div>
</section>
