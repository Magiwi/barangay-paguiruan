@extends('layouts.admin')

@section('title', 'Home & contact (CMS) - Admin')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl space-y-8">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Home & contact</h1>
            <p class="mt-1 text-sm text-gray-600">Edits the public landing page (hero, about preview, map/contact, closing CTA), the public footer contact lines, and—when “Use Site Settings” is enabled—the About page contact block and office hours line.</p>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if ($errors->any())
            <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ route('admin.site-settings.update') }}" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-sm font-semibold text-gray-900">Hero</h2>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Badge</label>
                    <input type="text" name="welcome_hero_badge" value="{{ old('welcome_hero_badge', $settings['welcome_hero_badge']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Title line 1</label>
                        <input type="text" name="welcome_hero_title_line1" value="{{ old('welcome_hero_title_line1', $settings['welcome_hero_title_line1']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Title line 2 (accent)</label>
                        <input type="text" name="welcome_hero_title_line2" value="{{ old('welcome_hero_title_line2', $settings['welcome_hero_title_line2']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Subtitle</label>
                    <textarea name="welcome_hero_subtitle" rows="3" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('welcome_hero_subtitle', $settings['welcome_hero_subtitle']) }}</textarea>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-sm font-semibold text-gray-900">About preview (home)</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Kicker</label>
                        <input type="text" name="welcome_about_kicker" value="{{ old('welcome_about_kicker', $settings['welcome_about_kicker']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Heading</label>
                        <input type="text" name="welcome_about_heading" value="{{ old('welcome_about_heading', $settings['welcome_about_heading']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Body</label>
                    <textarea name="welcome_about_body" rows="4" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('welcome_about_body', $settings['welcome_about_body']) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Bullet points (one per line)</label>
                    <textarea name="welcome_about_bullets" rows="4" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono text-xs">{{ old('welcome_about_bullets', $settings['welcome_about_bullets']) }}</textarea>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-sm font-semibold text-gray-900">Contact / map</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Section kicker</label>
                        <input type="text" name="contact_section_kicker" value="{{ old('contact_section_kicker', $settings['contact_section_kicker']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Section heading</label>
                        <input type="text" name="contact_section_heading" value="{{ old('contact_section_heading', $settings['contact_section_heading']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Address (one line)</label>
                    <input type="text" name="contact_address_line" value="{{ old('contact_address_line', $settings['contact_address_line']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Phone</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Email</label>
                        <input type="text" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Google Maps embed URL (iframe <code class="text-xs">src</code>)</label>
                    <input type="url" name="contact_maps_embed_url" value="{{ old('contact_maps_embed_url', $settings['contact_maps_embed_url']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Office hours (shown on home contact cards and on the About page when using Site Settings)</label>
                    <input type="text" name="contact_office_hours" value="{{ old('contact_office_hours', $settings['contact_office_hours'] ?? '') }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="e.g. Office Hours: Monday – Friday, 8:00 AM – 5:00 PM">
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-sm font-semibold text-gray-900">Closing CTA</h2>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Heading</label>
                    <input type="text" name="welcome_cta_heading" value="{{ old('welcome_cta_heading', $settings['welcome_cta_heading']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Subtitle</label>
                    <textarea name="welcome_cta_subtitle" rows="3" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('welcome_cta_subtitle', $settings['welcome_cta_subtitle']) }}</textarea>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-sm font-semibold text-gray-900">Certificates &amp; permits (PDF)</h2>
                <p class="text-xs text-gray-500">Header lines, seal note, jurisdiction phrases, and disclaimers used on printed certificates and permits. Punong Barangay, kagawad, SK, treasurer, and secretary on PDFs are loaded from the Officials roster (currently serving).</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Header line 1</label>
                        <input type="text" name="doc_header_line_1" value="{{ old('doc_header_line_1', $settings['doc_header_line_1']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Header line 2</label>
                        <input type="text" name="doc_header_line_2" value="{{ old('doc_header_line_2', $settings['doc_header_line_2']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Header line 3</label>
                        <input type="text" name="doc_header_line_3" value="{{ old('doc_header_line_3', $settings['doc_header_line_3']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Header line 4</label>
                        <input type="text" name="doc_header_line_4" value="{{ old('doc_header_line_4', $settings['doc_header_line_4']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Office line (below header)</label>
                    <input type="text" name="doc_header_office_line" value="{{ old('doc_header_office_line', $settings['doc_header_office_line']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Seal note</label>
                    <input type="text" name="doc_seal_note" value="{{ old('doc_seal_note', $settings['doc_seal_note']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Jurisdiction (short, e.g. after street address)</label>
                    <input type="text" name="doc_jurisdiction_short" value="{{ old('doc_jurisdiction_short', $settings['doc_jurisdiction_short']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Jurisdiction (medium, e.g. after Purok)</label>
                    <input type="text" name="doc_jurisdiction_medium" value="{{ old('doc_jurisdiction_medium', $settings['doc_jurisdiction_medium']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Barangay certificate — legal purpose clause</label>
                    <textarea name="doc_cert_legal_purpose_clause" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('doc_cert_legal_purpose_clause', $settings['doc_cert_legal_purpose_clause']) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Permit disclaimer (building, event, etc.)</label>
                    <textarea name="doc_permit_disclaimer_general" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('doc_permit_disclaimer_general', $settings['doc_permit_disclaimer_general']) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Permit disclaimer (business)</label>
                    <textarea name="doc_permit_disclaimer_business" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('doc_permit_disclaimer_business', $settings['doc_permit_disclaimer_business']) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">“Issued at …” closing (after the date; include leading comma if needed)</label>
                    <input type="text" name="doc_issued_at_suffix" value="{{ old('doc_issued_at_suffix', $settings['doc_issued_at_suffix']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="at Barangay …">
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-sm font-semibold text-gray-900">Blotter, summons &amp; reports</h2>
                <p class="text-xs text-gray-500">KP/Lupon summon PDFs, blotter notices, and admin report/Excel headers use the four header lines above; these fields add KP titles and signatory wording. Summon signature name follows the current Punong Barangay from Officials.</p>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Lupon office title (summon form)</label>
                    <input type="text" name="doc_lupon_office_title" value="{{ old('doc_lupon_office_title', $settings['doc_lupon_office_title']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Summon / blotter signatory role line</label>
                    <input type="text" name="doc_summon_signatory_role" value="{{ old('doc_summon_signatory_role', $settings['doc_summon_signatory_role']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Summon notice subtitle</label>
                    <input type="text" name="doc_blotter_summon_subtitle" value="{{ old('doc_blotter_summon_subtitle', $settings['doc_blotter_summon_subtitle']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Certification to file action — subtitle</label>
                    <input type="text" name="doc_blotter_certification_subtitle" value="{{ old('doc_blotter_certification_subtitle', $settings['doc_blotter_certification_subtitle']) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg rounded-lg">Save site content</button>
            </div>
        </form>
    </div>
</section>
@endsection
