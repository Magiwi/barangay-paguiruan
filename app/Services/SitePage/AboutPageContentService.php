<?php

namespace App\Services\SitePage;

use App\Models\SitePageLayout;
use App\Models\SiteSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class AboutPageContentService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function getDraftSectionsForBuilder(): array
    {
        $layout = $this->getOrCreateAboutLayout();
        $draft = $layout->draft_sections;

        if (! is_array($draft) || $draft === []) {
            return AboutPageDefaults::sections();
        }

        return $this->normalizeAndValidate($draft, mergeDefaults: true);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPublishedSectionsForPublic(): array
    {
        $layout = $this->getOrCreateAboutLayout();

        if ($layout->hasPublishedSnapshot()) {
            /** @var array<int, mixed> $pub */
            $pub = $layout->published_sections ?? [];

            return $this->normalizeAndValidate($pub, mergeDefaults: false);
        }

        return AboutPageDefaults::sections();
    }

    public function getOrCreateAboutLayout(): SitePageLayout
    {
        return SitePageLayout::query()->firstOrCreate(
            ['page_key' => SitePageLayout::PAGE_ABOUT],
            [
                'draft_sections' => AboutPageDefaults::sections(),
                'published_sections' => null,
                'published_at' => null,
            ]
        );
    }

    /**
     * @param  list<array<string, mixed>>  $sections
     */
    public function hasAtLeastOneVisibleSection(array $sections): bool
    {
        foreach ($sections as $section) {
            if (! empty($section['visible'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $sections
     * @return list<array<string, mixed>>
     */
    public function normalizeAndValidate(array $sections, bool $mergeDefaults): array
    {
        $defaults = AboutPageDefaults::sections();
        $defaultById = [];
        foreach ($defaults as $d) {
            $defaultById[$d['id']] = $d;
        }

        $out = [];
        foreach ($sections as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $id = isset($row['id']) && is_string($row['id']) ? $row['id'] : 'sec-'.($index + 1);
            $type = $row['type'] ?? null;
            if (! is_string($type) || ! in_array($type, AboutPageDefaults::SECTION_TYPES, true)) {
                continue;
            }

            $visible = array_key_exists('visible', $row)
                ? (bool) $row['visible']
                : true;

            $data = isset($row['data']) && is_array($row['data']) ? $row['data'] : [];

            if ($mergeDefaults && isset($defaultById[$id])) {
                $data = $this->mergeDataForType($type, $defaultById[$id]['data'] ?? [], $data);
            } else {
                $data = $this->mergeDataForType($type, $this->minimalDataForType($type), $data);
            }

            $this->assertValidSection($type, $data);

            $out[] = [
                'id' => $id,
                'type' => $type,
                'visible' => $visible,
                'data' => $data,
            ];
        }

        if ($out === []) {
            return AboutPageDefaults::sections();
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function mergeDataForType(string $type, array $base, array $overrides): array
    {
        $merged = array_replace_recursive($base, $overrides);

        return match ($type) {
            'hero' => $this->trimHero($merged),
            'intro' => $this->trimIntro($merged),
            'location' => $this->trimLocation($merged),
            'gallery' => $this->trimGallery($merged),
            'stats' => $this->trimStats($merged),
            'mission_vision' => $this->trimMission($merged),
            'priorities' => $this->trimPriorities($merged),
            'officials' => $this->trimOfficials($merged),
            'contact' => $this->trimContact($merged),
            default => $merged,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function minimalDataForType(string $type): array
    {
        foreach (AboutPageDefaults::sections() as $s) {
            if ($s['type'] === $type) {
                return $s['data'];
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertValidSection(string $type, array $data): void
    {
        $rules = match ($type) {
            'hero' => [
                'badge' => ['nullable', 'string', 'max:500'],
                'title_line1' => ['nullable', 'string', 'max:500'],
                'title_line2' => ['nullable', 'string', 'max:500'],
                'subtitle' => ['nullable', 'string', 'max:5000'],
                'primary_button_label' => ['nullable', 'string', 'max:200'],
                'primary_button_href' => ['nullable', 'string', 'max:500'],
                'secondary_button_label' => ['nullable', 'string', 'max:200'],
                'secondary_button_href' => ['nullable', 'string', 'max:500'],
                'hero_visual' => ['nullable', Rule::in(['icon', 'image'])],
                'hero_image_path' => ['nullable', 'string', 'max:500'],
            ],
            'intro' => [
                'kicker' => ['nullable', 'string', 'max:500'],
                'heading' => ['nullable', 'string', 'max:500'],
                'body' => ['nullable', 'string', 'max:10000'],
                'features' => ['nullable', 'array', 'max:6'],
                'features.*.accent' => ['nullable', Rule::in(['blue', 'green'])],
                'features.*.title' => ['nullable', 'string', 'max:500'],
                'features.*.body' => ['nullable', 'string', 'max:5000'],
            ],
            'location' => [
                'kicker' => ['nullable', 'string', 'max:500'],
                'heading' => ['nullable', 'string', 'max:500'],
                'paragraphs' => ['nullable', 'array', 'max:20'],
                'paragraphs.*' => ['nullable', 'string', 'max:5000'],
                'bullets' => ['nullable', 'array', 'max:30'],
                'bullets.*' => ['nullable', 'string', 'max:1000'],
                'purok_label' => ['nullable', 'string', 'max:200'],
                'purok_options' => ['nullable', 'array', 'max:30'],
                'purok_options.*.value' => ['nullable', 'string', 'max:100'],
                'purok_options.*.label' => ['nullable', 'string', 'max:500'],
                'purok_options.*.map_embed_url' => ['nullable', 'string', 'max:2000'],
                'map_embed_url' => ['nullable', 'string', 'max:2000'],
                'map_caption' => ['nullable', 'string', 'max:500'],
            ],
            'gallery' => [
                'kicker' => ['nullable', 'string', 'max:500'],
                'heading' => ['nullable', 'string', 'max:500'],
                'subtitle' => ['nullable', 'string', 'max:2000'],
                'slides' => ['nullable', 'array', 'max:24'],
                'slides.*.path' => ['nullable', 'string', 'max:1000'],
                'slides.*.label' => ['nullable', 'string', 'max:500'],
                'slides.*.alt' => ['nullable', 'string', 'max:500'],
            ],
            'stats' => [
                'kicker' => ['nullable', 'string', 'max:500'],
                'heading' => ['nullable', 'string', 'max:500'],
                'labels.residents' => ['nullable', 'string', 'max:200'],
                'labels.households' => ['nullable', 'string', 'max:200'],
                'labels.puroks' => ['nullable', 'string', 'max:200'],
            ],
            'mission_vision' => [
                'kicker' => ['nullable', 'string', 'max:500'],
                'heading' => ['nullable', 'string', 'max:500'],
                'mission_title' => ['nullable', 'string', 'max:500'],
                'mission_body' => ['nullable', 'string', 'max:10000'],
                'vision_title' => ['nullable', 'string', 'max:500'],
                'vision_body' => ['nullable', 'string', 'max:10000'],
            ],
            'priorities' => [
                'kicker' => ['nullable', 'string', 'max:500'],
                'heading' => ['nullable', 'string', 'max:500'],
                'intro' => ['nullable', 'string', 'max:10000'],
                'cards' => ['nullable', 'array', 'max:12'],
                'cards.*.title' => ['nullable', 'string', 'max:500'],
                'cards.*.body' => ['nullable', 'string', 'max:5000'],
            ],
            'officials' => [
                'kicker' => ['nullable', 'string', 'max:500'],
                'heading' => ['nullable', 'string', 'max:500'],
                'subtitle' => ['nullable', 'string', 'max:2000'],
            ],
            'contact' => [
                'kicker' => ['nullable', 'string', 'max:500'],
                'heading' => ['nullable', 'string', 'max:500'],
                'use_site_settings' => ['boolean'],
                'label_address' => ['nullable', 'string', 'max:200'],
                'label_phone' => ['nullable', 'string', 'max:200'],
                'label_email' => ['nullable', 'string', 'max:200'],
                'manual_address_html' => ['nullable', 'string', 'max:2000'],
                'manual_phone' => ['nullable', 'string', 'max:200'],
                'manual_email' => ['nullable', 'string', 'max:200'],
                'office_hours_line' => ['nullable', 'string', 'max:500'],
            ],
            default => [],
        };

        $v = Validator::make($data, $rules);
        if ($v->fails()) {
            throw ValidationException::withMessages($v->errors()->toArray());
        }
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function trimHero(array $d): array
    {
        $d['hero_visual'] = ($d['hero_visual'] ?? 'icon') === 'image' ? 'image' : 'icon';

        return $d;
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function trimIntro(array $d): array
    {
        $features = array_values(array_filter(
            Arr::wrap($d['features'] ?? []),
            fn ($f) => is_array($f)
        ));
        $d['features'] = array_slice($features, 0, 6);

        return $d;
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function trimLocation(array $d): array
    {
        $opts = array_values(array_filter(
            Arr::wrap($d['purok_options'] ?? []),
            fn ($o) => is_array($o) && trim((string) ($o['value'] ?? '')) !== ''
        ));
        $d['purok_options'] = array_slice($opts, 0, 20);

        if ($d['purok_options'] === []) {
            $fallback = $this->minimalDataForType('location');
            $d['purok_options'] = $fallback['purok_options'] ?? [];
        }

        return $d;
    }

    /**
     * Merge built-in purok map URLs with per-option overrides from the Location section.
     *
     * @param  array<string, mixed>  $locationData
     * @return array<string, string>
     */
    public function resolvePurokMapUrlsForLocation(array $locationData): array
    {
        $maps = AboutPageDefaults::defaultPurokMapUrls();

        foreach ($locationData['purok_options'] ?? [] as $opt) {
            if (! is_array($opt)) {
                continue;
            }
            $value = trim((string) ($opt['value'] ?? ''));
            if ($value === '') {
                continue;
            }
            $url = isset($opt['map_embed_url']) && is_string($opt['map_embed_url'])
                ? trim($opt['map_embed_url'])
                : '';
            if ($url !== '') {
                $maps[$value] = $url;
            }
        }

        return $maps;
    }

    /**
     * @param  list<array<string, mixed>>  $sections
     * @return array<string, string>
     */
    public function resolvePurokMapUrlsFromSections(array $sections): array
    {
        foreach ($sections as $section) {
            if (($section['type'] ?? '') === 'location' && ! empty($section['visible'])) {
                /** @var array<string, mixed> $data */
                $data = $section['data'] ?? [];

                return $this->resolvePurokMapUrlsForLocation($data);
            }
        }

        foreach ($sections as $section) {
            if (($section['type'] ?? '') === 'location') {
                /** @var array<string, mixed> $data */
                $data = $section['data'] ?? [];

                return $this->resolvePurokMapUrlsForLocation($data);
            }
        }

        return AboutPageDefaults::defaultPurokMapUrls();
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function trimGallery(array $d): array
    {
        $slides = array_values(array_filter(
            Arr::wrap($d['slides'] ?? []),
            fn ($s) => is_array($s)
        ));
        $slides = array_values(array_filter(
            $slides,
            fn ($s) => trim((string) ($s['path'] ?? '')) !== ''
        ));
        $d['slides'] = array_slice($slides, 0, 24);

        return $d;
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function trimStats(array $d): array
    {
        $d['labels'] = array_merge(
            [
                'residents' => 'Total Residents',
                'households' => 'Households',
                'puroks' => 'Puroks (Zones)',
            ],
            is_array($d['labels'] ?? null) ? $d['labels'] : []
        );

        return $d;
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function trimMission(array $d): array
    {
        return $d;
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function trimPriorities(array $d): array
    {
        $cards = array_values(array_filter(
            Arr::wrap($d['cards'] ?? []),
            fn ($c) => is_array($c)
        ));
        $d['cards'] = array_slice($cards, 0, 12);

        return $d;
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function trimOfficials(array $d): array
    {
        return $d;
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function trimContact(array $d): array
    {
        $d['use_site_settings'] = (bool) ($d['use_site_settings'] ?? true);

        return $d;
    }

    /**
     * Resolve map iframe src for location section.
     *
     * @param  array<string, mixed>  $locationData
     */
    public function resolveMapEmbedUrl(array $locationData): string
    {
        $custom = isset($locationData['map_embed_url']) && is_string($locationData['map_embed_url'])
            ? trim($locationData['map_embed_url'])
            : '';

        if ($custom !== '') {
            return $custom;
        }

        $fromSettings = SiteSetting::getValue('contact_maps_embed_url', '');
        if ($fromSettings !== '') {
            return $fromSettings;
        }

        return AboutPageDefaults::defaultPurokMapUrls()['default'];
    }

    /**
     * Contact values for About page (single source when use_site_settings).
     *
     * @param  array<string, mixed>  $contactData
     * @return array{address: string, phone: string, email: string}
     */
    public function resolveContactValues(array $contactData): array
    {
        $useSettings = (bool) ($contactData['use_site_settings'] ?? true);

        if ($useSettings) {
            return [
                'address' => SiteSetting::getValue('contact_address_line', ''),
                'phone' => SiteSetting::getValue('contact_phone', ''),
                'email' => SiteSetting::getValue('contact_email', ''),
            ];
        }

        $addr = isset($contactData['manual_address_html']) && is_string($contactData['manual_address_html'])
            ? $contactData['manual_address_html']
            : '';
        $phone = isset($contactData['manual_phone']) && is_string($contactData['manual_phone'])
            ? $contactData['manual_phone']
            : '';
        $email = isset($contactData['manual_email']) && is_string($contactData['manual_email'])
            ? $contactData['manual_email']
            : '';

        return [
            'address' => $addr,
            'phone' => $phone,
            'email' => $email,
        ];
    }
}
