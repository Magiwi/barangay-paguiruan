@php
    use App\Support\AboutPageMedia;
    $heroUrl = AboutPageMedia::url($data['hero_image_path'] ?? null);
    $useImage = ($data['hero_visual'] ?? 'icon') === 'image' && $heroUrl;
@endphp
<section class="relative overflow-hidden bg-gradient-to-r from-blue-800 via-blue-700 to-green-600">
    <div class="absolute inset-0 opacity-[0.07]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;40&quot; height=&quot;40&quot; viewBox=&quot;0 0 40 40&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;%23fff&quot; fill-rule=&quot;evenodd&quot;%3E%3Cpath d=&quot;M0 40L40 0H20L0 20M40 40V20L20 40&quot;/%3E%3C/g%3E%3C/svg%3E');"></div>

    <div class="relative mx-auto max-w-7xl px-6 py-20 sm:py-28 lg:py-32">
        <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2">
            <div class="fade-in">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-medium text-blue-100 ring-1 ring-white/20 backdrop-blur-sm">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18"/></svg>
                    {{ $data['badge'] ?? '' }}
                </div>
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-[3.5rem] lg:leading-[1.15]">
                    {{ $data['title_line1'] ?? '' }}<br>{{ $data['title_line2'] ?? '' }}
                </h1>
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-blue-100/90">
                    {{ $data['subtitle'] ?? '' }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $data['primary_button_href'] ?? '#about-system' }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-sm font-semibold text-blue-700 shadow-lg shadow-blue-900/20 hover:bg-gray-50 transition-all duration-300">
                        {{ $data['primary_button_label'] ?? 'Learn More' }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                    </a>
                    <a href="{{ $data['secondary_button_href'] ?? '#contact' }}" class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-6 py-3 text-sm font-semibold text-white ring-1 ring-white/20 hover:bg-white/20 backdrop-blur-sm transition-all duration-300">
                        {{ $data['secondary_button_label'] ?? 'Contact Us' }}
                    </a>
                </div>
            </div>

            <div class="hidden lg:flex justify-end fade-in fade-delay-2">
                @if($useImage)
                    <div class="relative">
                        <div class="absolute -inset-4 rounded-full bg-white/5 blur-2xl"></div>
                        <img src="{{ $heroUrl }}" alt="" class="relative h-72 w-72 rounded-2xl object-cover shadow-2xl ring-1 ring-white/20">
                    </div>
                @else
                    <div class="relative">
                        <div class="absolute -inset-4 rounded-full bg-white/5 blur-2xl"></div>
                        <div class="relative flex h-64 w-64 items-center justify-center rounded-full border border-white/10 bg-white/5 backdrop-blur-sm">
                            <div class="flex h-48 w-48 items-center justify-center rounded-full border border-white/10 bg-white/5">
                                <div class="flex h-32 w-32 items-center justify-center rounded-full bg-white/10">
                                    <svg class="h-16 w-16 text-white/80" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
            <path d="M0 80V40C240 70 480 10 720 40C960 70 1200 10 1440 40V80H0Z" fill="#F9FAFB"/>
        </svg>
    </div>
</section>
