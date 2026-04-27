<?php

namespace App\Http\Controllers;

use App\Models\Official;
use App\Models\Purok;
use App\Models\User;
use App\Services\SitePage\AboutPageContentService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __construct(
        private AboutPageContentService $aboutPageContent
    ) {}

    public function show(): View
    {
        $sections = $this->aboutPageContent->getPublishedSectionsForPublic();

        return view('about.show', $this->viewPayload($sections));
    }

    /**
     * @param  list<array<string, mixed>>  $sections
     * @return array<string, mixed>
     */
    public function viewPayload(array $sections): array
    {
        return [
            'sections' => $sections,
            'communityStats' => $this->communityStats(),
            'officialCards' => $this->officialCards(),
            'purokMapUrls' => $this->aboutPageContent->resolvePurokMapUrlsFromSections($sections),
        ];
    }

    /**
     * @return array{total_people: int, total_households: int, total_puroks: int}
     */
    private function communityStats(): array
    {
        return [
            'total_people' => User::countable()
                ->where('status', User::STATUS_APPROVED)
                ->count(),
            'total_households' => User::countable()
                ->where('status', User::STATUS_APPROVED)
                ->where('head_of_family', 'yes')
                ->count(),
            'total_puroks' => Purok::active()->count(),
        ];
    }

    /**
     * @return Collection<int, array{name: string, role: string, initials: string}>
     */
    private function officialCards()
    {
        return Official::query()
            ->with([
                'user:id,first_name,middle_name,last_name,suffix',
                'position:id,name,sort_order',
            ])
            ->currentlyServing()
            ->get()
            ->sortBy(function ($official) {
                return [
                    $official->position->sort_order ?? 999,
                    $official->position->name ?? '',
                    $official->user->last_name ?? '',
                    $official->user->first_name ?? '',
                ];
            })
            ->values()
            ->map(function ($official) {
                $fullName = $official->user?->full_name
                    ?? trim(implode(' ', array_filter([
                        $official->user?->first_name,
                        $official->user?->middle_name,
                        $official->user?->last_name,
                        $official->user?->suffix,
                    ])));

                $nameParts = preg_split('/\s+/', trim((string) $fullName)) ?: [];
                $initials = collect($nameParts)
                    ->filter()
                    ->take(2)
                    ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                    ->implode('');

                return [
                    'name' => $fullName !== '' ? $fullName : 'Barangay Official',
                    'role' => $official->position->name ?? 'Barangay Official',
                    'initials' => $initials !== '' ? $initials : 'BO',
                ];
            });
    }
}
