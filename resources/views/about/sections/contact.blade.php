@php
    use App\Models\SiteSetting;
    use App\Services\SitePage\AboutPageContentService;
    $contact = app(AboutPageContentService::class)->resolveContactValues($data);
    $useSettings = $data['use_site_settings'] ?? true;
    $officeLine = $useSettings
        ? SiteSetting::getValue('contact_office_hours', '')
        : (string) ($data['office_hours_line'] ?? '');
    $phoneDigits = isset($contact['phone']) ? preg_replace('/\D+/', '', (string) $contact['phone']) : '';
@endphp
<section id="contact" class="bg-gray-50 py-16 px-6">
    <div class="mx-auto max-w-7xl">
        <div class="text-center mb-12 fade-in">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">{{ $data['kicker'] ?? '' }}</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $data['heading'] ?? '' }}</h2>
        </div>
        <div class="fade-in mx-auto max-w-4xl rounded-2xl bg-white shadow-md ring-1 ring-gray-100 overflow-hidden">
            <div class="grid grid-cols-1 divide-y md:grid-cols-3 md:divide-y-0 md:divide-x divide-gray-100">
                <div class="flex items-start gap-4 p-7">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50">
                        <svg class="h-5 w-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $data['label_address'] ?? 'Address' }}</h3>
                        @if ($useSettings)
                            <p class="mt-1 text-sm text-gray-600 leading-relaxed">{{ $contact['address'] }}</p>
                        @else
                            <p class="mt-1 text-sm text-gray-600 leading-relaxed">{!! $data['manual_address_html'] ?? '' !!}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-start gap-4 p-7">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-50">
                        <svg class="h-5 w-5 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $data['label_phone'] ?? 'Phone' }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            @if($phoneDigits !== '')
                                <a href="tel:{{ $phoneDigits }}" class="text-blue-700 hover:underline">{{ $contact['phone'] }}</a>
                            @else
                                {{ $contact['phone'] }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-7">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50">
                        <svg class="h-5 w-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $data['label_email'] ?? 'Email' }}</h3>
                        <p class="mt-1 text-sm text-gray-600 break-all">
                            @if(trim((string) ($contact['email'] ?? '')) !== '')
                                <a href="mailto:{{ trim($contact['email']) }}" class="text-blue-700 hover:underline">{{ $contact['email'] }}</a>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-100 bg-gray-50 px-7 py-3">
                <p class="text-xs text-gray-500 flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $officeLine }}
                </p>
            </div>
        </div>
    </div>
</section>
