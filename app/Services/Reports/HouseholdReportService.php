<?php

namespace App\Services\Reports;

use App\DataTransferObjects\Reports\HouseholdReportFilters;
use App\Models\FamilyMember;
use App\Models\Household;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HouseholdReportService
{
    public function resolveFilters(Request $request): HouseholdReportFilters
    {
        $allowedSorts = ['head_name', 'purok', 'members', 'resident_type', 'status', 'created_at'];
        $purokId = $request->filled('purok') ? (int) $request->query('purok') : null;
        if ($purokId !== null && ! Purok::whereKey($purokId)->exists()) {
            $purokId = null;
        }

        $filters = new HouseholdReportFilters(
            purokId: $purokId,
            sort: in_array((string) $request->query('sort', 'head_name'), $allowedSorts, true)
                ? (string) $request->query('sort', 'head_name')
                : 'head_name',
            direction: strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc',
            selectedHeadId: $request->filled('head_id') ? (int) $request->query('head_id') : null,
            headQuery: trim((string) $request->query('head_q', '')),
            residentType: in_array((string) $request->query('resident_type', ''), ['permanent', 'non-permanent'], true)
                ? (string) $request->query('resident_type')
                : null,
            statusFilter: in_array((string) $request->query('household_status', ''), ['active', 'suspended'], true)
                ? (string) $request->query('household_status')
                : null,
            membersMin: $request->filled('members_min') ? max((int) $request->query('members_min'), 1) : null,
            membersMax: $request->filled('members_max') ? max((int) $request->query('members_max'), 1) : null,
            createdFrom: $this->normalizeHouseholdDate($request->query('created_from')),
            createdTo: $this->normalizeHouseholdDate($request->query('created_to'))
        );

        if ($filters->membersMin !== null && $filters->membersMax !== null && $filters->membersMax < $filters->membersMin) {
            [$filters->membersMin, $filters->membersMax] = [$filters->membersMax, $filters->membersMin];
        }

        if ($filters->selectedHeadId !== null) {
            $selectedHeadExists = User::countable()
                ->where('status', User::STATUS_APPROVED)
                ->where('head_of_family', 'yes')
                ->when($filters->purokId, fn ($q) => $q->where('purok_id', $filters->purokId))
                ->whereKey($filters->selectedHeadId)
                ->exists();

            if (! $selectedHeadExists) {
                $filters->selectedHeadId = null;
            }
        }

        return $filters;
    }

    public function buildDashboardData(HouseholdReportFilters $filters): array
    {
        $startedAt = microtime(true);
        $allPuroks = Purok::orderBy('name')->get();
        $households = $this->buildHouseholdHeadsQuery($filters)
            ->with('purokRelation')
            ->with([
                'familyMembers' => fn ($query) => $query
                    ->where('status', User::STATUS_APPROVED)
                    ->where('is_suspended', false)
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->select([
                        'id',
                        'first_name',
                        'middle_name',
                        'last_name',
                        'suffix',
                        'head_of_family_id',
                        'household_id',
                        'relationship_to_head',
                    ]),
                'familyMemberRecords' => fn ($query) => $query
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->select([
                        'id',
                        'head_user_id',
                        'household_id',
                        'linked_user_id',
                        'first_name',
                        'middle_name',
                        'last_name',
                        'suffix',
                        'relationship_to_head',
                    ]),
            ])
            ->paginate(25)
            ->withQueryString();

        $aggregateSnapshot = $this->getHouseholdAggregateSnapshot($filters);
        $totalHouseholds = $aggregateSnapshot['totalHouseholds'];
        $totalMembers = $aggregateSnapshot['totalMembers'];
        $avgSize = $aggregateSnapshot['avgSize'];
        $householdsPerPurok = collect($aggregateSnapshot['householdsPerPurok']);
        $householdMembers = collect($aggregateSnapshot['householdMembers'])->mapWithKeys(
            fn ($row) => [
                (int) $row['id'] => (object) [
                    'id' => (int) $row['id'],
                    'name' => (string) $row['name'],
                    'member_count' => (int) $row['member_count'],
                ],
            ]
        );
        $largest = $aggregateSnapshot['largestId']
            ? User::with('purokRelation')->withCount([
                'familyMemberRecords as family_members_count',
                'familyMembers as linked_members_count',
            ])->find($aggregateSnapshot['largestId'])
            : null;
        $smallest = $aggregateSnapshot['smallestId']
            ? User::with('purokRelation')->withCount([
                'familyMemberRecords as family_members_count',
                'familyMembers as linked_members_count',
            ])->find($aggregateSnapshot['smallestId'])
            : null;
        $sizeBands = $aggregateSnapshot['sizeBands'];
        $statusDistribution = $aggregateSnapshot['statusDistribution'];
        $purokLabels = array_values(array_map(fn ($row) => (string) $row['name'], $aggregateSnapshot['householdsPerPurok']));
        $purokHouseholdSeries = array_values(array_map(fn ($row) => (int) $row['household_count'], $aggregateSnapshot['householdsPerPurok']));
        $purokMemberSeries = array_values(array_map(fn ($row) => (int) $row['member_count'], $aggregateSnapshot['householdMembers']));

        $this->logTiming('dashboard', $startedAt, $filters, [
            'households_count' => $households->count(),
            'total_households' => $totalHouseholds,
            'total_members' => $totalMembers,
        ]);

        return [
            'totalHouseholds' => $totalHouseholds,
            'totalMembers' => $totalMembers,
            'avgSize' => $avgSize,
            'householdsPerPurok' => $householdsPerPurok,
            'householdMembers' => $householdMembers,
            'households' => $households,
            'largest' => $largest,
            'smallest' => $smallest,
            'sort' => $filters->sort,
            'direction' => $filters->direction,
            'sizeBands' => $sizeBands,
            'statusDistribution' => $statusDistribution,
            'purokLabels' => $purokLabels,
            'purokHouseholdSeries' => $purokHouseholdSeries,
            'purokMemberSeries' => $purokMemberSeries,
            'allPuroks' => $allPuroks,
            'purokId' => $filters->purokId,
            'selectedHeadId' => $filters->selectedHeadId,
            'headQuery' => $filters->headQuery,
            'residentType' => $filters->residentType,
            'statusFilter' => $filters->statusFilter,
            'membersMin' => $filters->membersMin,
            'membersMax' => $filters->membersMax,
            'createdFrom' => $filters->createdFrom,
            'createdTo' => $filters->createdTo,
        ];
    }

    public function buildHouseholdHeadSuggestions(string $queryText, ?int $purokId = null): array
    {
        $queryText = trim($queryText);
        if ($queryText === '' || mb_strlen($queryText) < 2) {
            return [];
        }

        if ($purokId !== null && ! Purok::whereKey($purokId)->exists()) {
            $purokId = null;
        }

        $rows = User::countable()
            ->where('status', User::STATUS_APPROVED)
            ->where('head_of_family', 'yes')
            ->when($purokId, fn ($q) => $q->where('purok_id', (int) $purokId))
            ->where(fn ($q) => $this->applyHouseholdHeadSearch($q, $queryText))
            ->with('purokRelation:id,name')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(10)
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix', 'purok_id', 'household_id', 'house_no']);

        return $rows->map(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $this->formatHeadSuggestionName($user),
                'purok' => $user->purokRelation?->name,
                'house_id' => $user->household_id ?: $user->house_no,
            ];
        })->values()->all();
    }

    public function buildExportRows(HouseholdReportFilters $filters): array
    {
        $households = Household::query()
            ->with([
                'head' => fn ($query) => $query->with('purokRelation:id,name'),
                'members' => fn ($query) => $query
                    ->where('status', User::STATUS_APPROVED)
                    ->where('is_suspended', false)
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->select([
                        'id',
                        'first_name',
                        'middle_name',
                        'last_name',
                        'suffix',
                        'role',
                        'status',
                        'is_suspended',
                        'household_id',
                    ]),
                'memberRecords' => fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->select([
                        'id',
                        'head_user_id',
                        'household_id',
                        'linked_user_id',
                        'first_name',
                        'middle_name',
                        'last_name',
                        'suffix',
                    ]),
            ])
            ->whereHas('head', function (Builder $query) use ($filters): void {
                $query->countable()
                    ->where('status', User::STATUS_APPROVED)
                    ->where('head_of_family', 'yes')
                    ->when($filters->purokId, fn ($q) => $q->where('purok_id', $filters->purokId))
                    ->when($filters->selectedHeadId, fn ($q) => $q->where('id', $filters->selectedHeadId))
                    ->when(! $filters->selectedHeadId && $filters->headQuery !== '', fn ($q) => $this->applyHouseholdHeadSearch($q, $filters->headQuery))
                    ->when($filters->residentType, fn ($q) => $q->where('resident_type', $filters->residentType))
                    ->when($filters->statusFilter === 'active', fn ($q) => $q->where('is_suspended', false))
                    ->when($filters->statusFilter === 'suspended', fn ($q) => $q->where('is_suspended', true))
                    ->when($filters->createdFrom, fn ($q) => $q->whereDate('created_at', '>=', $filters->createdFrom))
                    ->when($filters->createdTo, fn ($q) => $q->whereDate('created_at', '<=', $filters->createdTo));
            })
            ->get();

        $rows = $households
            ->unique('id')
            ->values()
            ->map(function (Household $household) {
            $head = $household->head;
            if (! $head) {
                return null;
            }

            $linkedMembers = $household->members
                ->filter(fn ($member) => (int) ($member->household_id ?? 0) === (int) $household->id)
                ->reject(fn ($member) => (int) ($member->id ?? 0) === (int) $head->id)
                ->values();

            $linkedMemberIds = $linkedMembers->pluck('id')->map(fn ($id) => (int) $id)->all();
            $recordMembers = $household->memberRecords
                ->filter(fn ($member) => (int) ($member->household_id ?? 0) === (int) $household->id)
                ->reject(fn ($member) => ! empty($member->linked_user_id) && in_array((int) $member->linked_user_id, $linkedMemberIds, true))
                ->values();

            $memberNames = collect();
            foreach ($linkedMembers as $member) {
                $memberNames->push($this->formatExportPersonName(
                    (string) ($member->first_name ?? ''),
                    (string) ($member->middle_name ?? ''),
                    (string) ($member->last_name ?? ''),
                    (string) ($member->suffix ?? '')
                ));
            }
            foreach ($recordMembers as $member) {
                $memberNames->push($this->formatExportPersonName(
                    (string) ($member->first_name ?? ''),
                    (string) ($member->middle_name ?? ''),
                    (string) ($member->last_name ?? ''),
                    (string) ($member->suffix ?? '')
                ));
            }

            $memberNames = $memberNames
                ->filter(fn ($name) => trim((string) $name) !== '')
                ->unique()
                ->values();

            $memberCount = $memberNames->count() + 1;

            $headDisplayName = $this->formatExportPersonName(
                (string) ($head->first_name ?? ''),
                (string) ($head->middle_name ?? ''),
                (string) ($head->last_name ?? ''),
                (string) ($head->suffix ?? '')
            );

            return [
                'household_id' => (int) $household->id,
                'head_name' => $headDisplayName,
                'head_name_upper' => mb_strtoupper($headDisplayName),
                'email' => (string) ($head->email ?? '—'),
                'purok' => (string) ($head->purokRelation?->name ?? '—'),
                'members' => $memberCount,
                'members_list' => $memberNames->all(),
                'resident_type' => ucfirst((string) ($head->resident_type ?? '—')),
                'status' => $head->is_suspended ? 'Suspended' : ucfirst((string) ($head->status ?? '—')),
                'registered_at' => optional($head->created_at)->format('M d, Y'),
                'sort_last_name' => (string) ($head->last_name ?? ''),
                'sort_first_name' => (string) ($head->first_name ?? ''),
                'sort_purok' => (string) ($head->purok ?? ''),
                'sort_status' => (string) ($head->status ?? ''),
                'sort_is_suspended' => (int) (($head->is_suspended ?? false) ? 1 : 0),
                'sort_created_at' => optional($head->created_at)?->timestamp ?? 0,
                'sort_resident_type' => (string) ($head->resident_type ?? ''),
            ];
        })->filter();

        $rows = $rows
            ->when($filters->membersMin !== null, fn (Collection $items) => $items->filter(
                fn (array $row) => (int) $row['members'] >= (int) $filters->membersMin
            ))
            ->when($filters->membersMax !== null, fn (Collection $items) => $items->filter(
                fn (array $row) => (int) $row['members'] <= (int) $filters->membersMax
            ))
            ->values();

        $sorted = match ($filters->sort) {
            'purok' => $rows->sortBy('sort_purok'),
            'members' => $rows->sortBy('members'),
            'resident_type' => $rows->sortBy('sort_resident_type'),
            'status' => $rows->sortBy(fn (array $row) => $row['sort_is_suspended'] . '|' . $row['sort_status']),
            'created_at' => $rows->sortBy('sort_created_at'),
            default => $rows->sortBy(fn (array $row) => mb_strtolower($row['sort_last_name'] . '|' . $row['sort_first_name'])),
        };

        if ($filters->direction === 'desc') {
            $sorted = $sorted->reverse()->values();
        } else {
            $sorted = $sorted->values();
        }

        return $sorted
            ->map(function (array $row): array {
                unset(
                    $row['sort_last_name'],
                    $row['sort_first_name'],
                    $row['sort_purok'],
                    $row['sort_status'],
                    $row['sort_is_suspended'],
                    $row['sort_created_at'],
                    $row['sort_resident_type']
                );

                return $row;
            })
            ->all();
    }

    private function formatExportPersonName(
        string $firstName,
        string $middleName,
        string $lastName,
        string $suffix
    ): string {
        $givenNames = trim(implode(' ', array_filter([$firstName, $middleName])));
        $formatted = trim($lastName . ($givenNames !== '' ? ', ' . $givenNames : ''));
        if ($suffix !== '') {
            $formatted = trim($formatted . ' ' . $suffix);
        }

        return $formatted !== '' ? $formatted : 'Unnamed member';
    }

    public function buildDetailViewData(HouseholdReportFilters $filters, Request $request): array
    {
        $startedAt = microtime(true);
        $viewSort = $this->resolveDetailSort((string) $request->query('view_sort', 'letter'));
        $viewOrder = $this->resolveDetailOrder((string) $request->query('view_order', 'asc'));
        $perPage = max(10, min(100, (int) $request->query('per_page', 50)));
        $currentPage = max(1, (int) $request->query('page', 1));

        $rows = $this->buildDetailRows($filters);
        $rows = $this->sortDetailRows($rows, $viewSort, $viewOrder);

        $total = $rows->count();
        $paginator = new LengthAwarePaginator(
            $rows->forPage($currentPage, $perPage)->values(),
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $allPuroks = Purok::orderBy('name')->get(['id', 'name']);
        $appliedFilters = array_values(array_filter([
            $filters->purokId ? 'Purok: ' . ($allPuroks->firstWhere('id', $filters->purokId)?->name ?? 'Unknown') : null,
            $filters->headQuery !== '' ? 'Head: ' . $filters->headQuery : null,
            $filters->residentType ? 'Type: ' . ucfirst($filters->residentType) : null,
            $filters->statusFilter ? 'Status: ' . ucfirst($filters->statusFilter) : null,
            $filters->membersMin !== null ? 'Members >= ' . $filters->membersMin : null,
            $filters->membersMax !== null ? 'Members <= ' . $filters->membersMax : null,
            $filters->createdFrom ? 'From: ' . $filters->createdFrom : null,
            $filters->createdTo ? 'To: ' . $filters->createdTo : null,
        ]));
        $householdHeadsCount = $rows->pluck('house_head')->unique()->count();
        $exportQuery = array_merge(
            $request->except(['page', 'per_page']),
            ['view_sort' => $viewSort, 'view_order' => $viewOrder]
        );

        $this->logTiming('detail_view', $startedAt, $filters, [
            'rows_total' => $total,
            'rows_per_page' => $perPage,
            'household_heads_count' => $householdHeadsCount,
        ]);

        return [
            'detailRows' => $paginator,
            'detailTotalRecords' => $total,
            'householdHeadsCount' => $householdHeadsCount,
            'viewSort' => $viewSort,
            'viewOrder' => $viewOrder,
            'perPage' => $perPage,
            'appliedFilters' => $appliedFilters,
            'reportType' => 'Household',
            'generatedAt' => now()->format('M d, Y h:i A'),
            'reportTitle' => $filters->headQuery !== '' ? $filters->headQuery : 'Household Family Member View',
            'reportScope' => 'family_members',
            'printQuery' => $exportQuery,
            'pdfQuery' => $exportQuery,
            'excelQuery' => $exportQuery,
        ];
    }

    public function buildDetailPrintData(HouseholdReportFilters $filters, Request $request): array
    {
        $startedAt = microtime(true);
        $viewSort = $this->resolveDetailSort((string) $request->query('view_sort', 'letter'));
        $viewOrder = $this->resolveDetailOrder((string) $request->query('view_order', 'asc'));
        $rows = $this->sortDetailRows($this->buildDetailRows($filters), $viewSort, $viewOrder);
        $allPuroks = Purok::orderBy('name')->get(['id', 'name']);
        $appliedFilters = array_values(array_filter([
            $filters->purokId ? 'Purok: ' . ($allPuroks->firstWhere('id', $filters->purokId)?->name ?? 'Unknown') : null,
            $filters->headQuery !== '' ? 'Head: ' . $filters->headQuery : null,
            $filters->residentType ? 'Type: ' . ucfirst($filters->residentType) : null,
            $filters->statusFilter ? 'Status: ' . ucfirst($filters->statusFilter) : null,
            $filters->membersMin !== null ? 'Members >= ' . $filters->membersMin : null,
            $filters->membersMax !== null ? 'Members <= ' . $filters->membersMax : null,
            $filters->createdFrom ? 'From: ' . $filters->createdFrom : null,
            $filters->createdTo ? 'To: ' . $filters->createdTo : null,
        ]));

        $this->logTiming('detail_print', $startedAt, $filters, [
            'rows_total' => $rows->count(),
            'household_heads_count' => $rows->pluck('house_head')->unique()->count(),
        ]);

        return [
            'rows' => $rows,
            'detailTotalRecords' => $rows->count(),
            'householdHeadsCount' => $rows->pluck('house_head')->unique()->count(),
            'reportType' => 'Household',
            'generatedAt' => now()->format('M d, Y h:i A'),
            'reportTitle' => $filters->headQuery !== '' ? $filters->headQuery : 'Household Family Member View',
            'reportScope' => 'family_members',
            'viewSort' => $viewSort,
            'viewOrder' => $viewOrder,
            'appliedFilters' => $appliedFilters,
        ];
    }

    public function buildHouseholdHeadsQuery(HouseholdReportFilters $filters): Builder
    {
        $query = User::countable()
            ->where('status', User::STATUS_APPROVED)
            ->where('head_of_family', 'yes')
            ->withCount([
                'familyMemberRecords as family_members_count',
                'familyMembers as linked_members_count',
            ]);

        $this->applyHouseholdCommonFilters($query, $filters);
        $this->applyHouseholdSorting($query, $filters);

        return $query;
    }

    private function buildDetailRows(HouseholdReportFilters $filters): Collection
    {
        $cacheKey = 'reports.households.detail_rows.' . md5(json_encode($filters->toArray(), JSON_THROW_ON_ERROR));
        $rawRows = Cache::remember($cacheKey, now()->addSeconds(45), function () use ($filters) {
            return $this->buildDetailRowsUncached($filters)->values()->all();
        });

        return collect($rawRows);
    }

    private function sortDetailRows(Collection $rows, string $viewSort, string $viewOrder): Collection
    {
        $sortKey = match ($viewSort) {
            'relationship' => 'relationship',
            'house_no' => 'house_no',
            default => 'house_head',
        };

        $sorted = $rows->sortBy(
            fn (array $row) => mb_strtolower((string) ($row[$sortKey] ?? '')) . '|' . mb_strtolower((string) ($row['family_member'] ?? ''))
        );

        return $viewOrder === 'desc' ? $sorted->reverse()->values() : $sorted->values();
    }

    private function resolveDetailSort(string $viewSort): string
    {
        return in_array($viewSort, ['letter', 'relationship', 'house_no'], true)
            ? $viewSort
            : 'letter';
    }

    private function resolveDetailOrder(string $viewOrder): string
    {
        return strtolower($viewOrder) === 'desc' ? 'desc' : 'asc';
    }

    private function buildDetailRowsUncached(HouseholdReportFilters $filters): Collection
    {
        $heads = $this->buildHouseholdHeadsQuery($filters)
            ->with([
                'familyMemberRecords' => fn ($q) => $q->orderBy('last_name')->orderBy('first_name'),
                'familyMembers' => fn ($q) => $q->orderBy('last_name')->orderBy('first_name'),
            ])
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'house_no']);

        $rows = collect();
        foreach ($heads as $head) {
            $headName = trim($head->last_name . ', ' . $head->first_name . ($head->middle_name ? ' ' . $head->middle_name : ''));
            if ($head->familyMemberRecords->isEmpty() && $head->familyMembers->isEmpty()) {
                $rows->push([
                    'house_head' => $headName,
                    'family_member' => 'No linked family members',
                    'relationship' => '—',
                    'house_no' => $head->house_no ?: 'N/A',
                ]);
                continue;
            }

            $recordLinkedUserIds = $head->familyMemberRecords
                ->pluck('linked_user_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($head->familyMemberRecords as $member) {
                $memberName = trim(implode(' ', array_filter([
                    $member->first_name,
                    $member->middle_name,
                    $member->last_name,
                    $member->suffix,
                ])));

                $rows->push([
                    'house_head' => $headName,
                    'family_member' => $memberName !== '' ? $memberName : 'Unnamed member',
                    'relationship' => $member->relationship_to_head ?: '—',
                    'house_no' => $member->house_no ?: ($head->house_no ?: 'N/A'),
                ]);
            }

            foreach ($head->familyMembers as $linkedMember) {
                if (in_array((int) $linkedMember->id, $recordLinkedUserIds, true)) {
                    continue;
                }

                $linkedName = trim(implode(' ', array_filter([
                    $linkedMember->first_name,
                    $linkedMember->middle_name,
                    $linkedMember->last_name,
                    $linkedMember->suffix,
                ])));

                $rows->push([
                    'house_head' => $headName,
                    'family_member' => $linkedName !== '' ? $linkedName : 'Unnamed member',
                    'relationship' => $linkedMember->relationship_to_head ?: '—',
                    'house_no' => $linkedMember->house_no ?: ($head->house_no ?: 'N/A'),
                ]);
            }
        }

        return $rows;
    }

    private function logTiming(string $context, float $startedAt, HouseholdReportFilters $filters, array $metrics = []): void
    {
        if (! config('app.debug')) {
            return;
        }

        Log::debug('Household report timing', array_merge([
            'context' => $context,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'filters' => $filters->toArray(),
        ], $metrics));
    }

    private function getHouseholdAggregateSnapshot(HouseholdReportFilters $filters): array
    {
        $cacheKey = 'reports.households.aggregate.' . md5(json_encode([
            'purok' => $filters->purokId,
            'head' => $filters->selectedHeadId,
            'head_q' => $filters->headQuery,
            'resident_type' => $filters->residentType,
            'status' => $filters->statusFilter,
            'members_min' => $filters->membersMin,
            'members_max' => $filters->membersMax,
            'from' => $filters->createdFrom,
            'to' => $filters->createdTo,
        ], JSON_THROW_ON_ERROR));

        return Cache::remember($cacheKey, now()->addSeconds(45), function () use ($filters) {
            $baseQuery = User::countable()
                ->where('status', User::STATUS_APPROVED)
                ->where('head_of_family', 'yes')
                ->withCount([
                    'familyMemberRecords as family_members_count',
                    'familyMembers as linked_members_count',
                ]);

            $this->applyHouseholdCommonFilters($baseQuery, $filters);

            $heads = (clone $baseQuery)->get(['id', 'purok_id', 'last_name', 'first_name', 'is_suspended']);
            $totalHouseholds = $heads->count();
            $totalMembers = $heads->sum(fn ($head) => (int) $head->family_members_count + (int) $head->linked_members_count + 1);
            $avgSize = $totalHouseholds > 0 ? round($totalMembers / $totalHouseholds, 1) : 0;

            $largest = $heads->sortByDesc(fn ($head) => (int) $head->family_members_count + (int) $head->linked_members_count)->first();
            $smallest = $heads->sortBy(fn ($head) => (int) $head->family_members_count + (int) $head->linked_members_count)->first();

            $sizeBands = [
                '1-2 Members' => 0,
                '3-4 Members' => 0,
                '5-6 Members' => 0,
                '7+ Members' => 0,
            ];
            foreach ($heads as $head) {
                $size = (int) $head->family_members_count + (int) $head->linked_members_count + 1;
                if ($size <= 2) {
                    $sizeBands['1-2 Members']++;
                } elseif ($size <= 4) {
                    $sizeBands['3-4 Members']++;
                } elseif ($size <= 6) {
                    $sizeBands['5-6 Members']++;
                } else {
                    $sizeBands['7+ Members']++;
                }
            }

            $statusDistribution = [
                'active' => $heads->filter(fn ($head) => ! (bool) $head->is_suspended)->count(),
                'pending' => 0,
                'suspended' => $heads->filter(fn ($head) => (bool) $head->is_suspended)->count(),
            ];

            $headCountsByPurok = $heads
                ->groupBy('purok_id')
                ->map(fn ($rows) => $rows->count());

            $headIds = $heads->pluck('id');
            $memberCountsByPurok = FamilyMember::query()
                ->whereIn('head_user_id', $headIds)
                ->selectRaw('purok_id, COUNT(*) as total')
                ->groupBy('purok_id')
                ->pluck('total', 'purok_id');

            $householdsPerPurok = Purok::query()
                ->when($filters->purokId, fn ($q) => $q->where('id', $filters->purokId))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($purok) => [
                    'id' => $purok->id,
                    'name' => $purok->name,
                    'household_count' => (int) ($headCountsByPurok[$purok->id] ?? 0),
                ])
                ->values()
                ->all();

            $householdMembers = collect($householdsPerPurok)->map(fn ($row) => [
                'id' => $row['id'],
                'name' => $row['name'],
                'member_count' => (int) (($headCountsByPurok[$row['id']] ?? 0) + ($memberCountsByPurok[$row['id']] ?? 0)),
            ])->values()->all();

            return [
                'totalHouseholds' => $totalHouseholds,
                'totalMembers' => $totalMembers,
                'avgSize' => $avgSize,
                'largestId' => $largest?->id,
                'smallestId' => $smallest?->id,
                'sizeBands' => $sizeBands,
                'statusDistribution' => $statusDistribution,
                'householdsPerPurok' => $householdsPerPurok,
                'householdMembers' => $householdMembers,
            ];
        });
    }

    private function applyHouseholdCommonFilters(Builder $query, HouseholdReportFilters $filters): Builder
    {
        $memberTotalSql = '(SELECT COUNT(*) FROM family_members WHERE family_members.head_user_id = users.id AND family_members.deleted_at IS NULL)'
            . ' + (SELECT COUNT(*) FROM users AS linked_members WHERE linked_members.head_of_family_id = users.id AND linked_members.role != "super_admin")'
            . ' + 1';

        $query
            ->when($filters->purokId, fn ($q) => $q->where('purok_id', $filters->purokId))
            ->when($filters->selectedHeadId, fn ($q) => $q->where('id', $filters->selectedHeadId))
            ->when(! $filters->selectedHeadId && $filters->headQuery !== '', fn ($q) => $this->applyHouseholdHeadSearch($q, $filters->headQuery))
            ->when($filters->residentType, fn ($q) => $q->where('resident_type', $filters->residentType))
            ->when($filters->statusFilter === 'active', fn ($q) => $q->where('is_suspended', false))
            ->when($filters->statusFilter === 'suspended', fn ($q) => $q->where('is_suspended', true))
            ->when($filters->createdFrom, fn ($q) => $q->whereDate('created_at', '>=', $filters->createdFrom))
            ->when($filters->createdTo, fn ($q) => $q->whereDate('created_at', '<=', $filters->createdTo))
            ->when($filters->membersMin !== null, fn ($q) => $q->whereRaw($memberTotalSql . ' >= ?', [$filters->membersMin]))
            ->when($filters->membersMax !== null, fn ($q) => $q->whereRaw($memberTotalSql . ' <= ?', [$filters->membersMax]));

        return $query;
    }

    private function applyHouseholdSorting(Builder $query, HouseholdReportFilters $filters): Builder
    {
        $sort = $filters->sort;
        $direction = $filters->direction;

        return $query
            ->when($sort === 'head_name', fn ($q) => $q->orderBy('last_name', $direction)->orderBy('first_name', $direction))
            ->when($sort === 'purok', fn ($q) => $q->orderBy('purok', $direction))
            ->when($sort === 'members', fn ($q) => $q->orderBy('family_members_count', $direction))
            ->when($sort === 'resident_type', fn ($q) => $q->orderBy('resident_type', $direction))
            ->when($sort === 'status', fn ($q) => $q->orderBy('is_suspended', $direction)->orderBy('status', $direction))
            ->when($sort === 'created_at', fn ($q) => $q->orderBy('created_at', $direction))
            ->when(! in_array($sort, ['head_name', 'purok', 'members', 'resident_type', 'status', 'created_at'], true), fn ($q) => $q->orderBy('last_name')->orderBy('first_name'));
    }

    private function formatHeadSuggestionName(User $user): string
    {
        $givenNames = trim(implode(' ', array_filter([$user->first_name, $user->middle_name])));
        $suffix = trim((string) ($user->suffix ?? ''));

        $name = trim($user->last_name . ', ' . $givenNames);
        if ($suffix !== '') {
            $name .= ' ' . $suffix;
        }

        return $name;
    }

    private function applyHouseholdHeadSearch(Builder $query, string $keyword): Builder
    {
        $trimmed = trim($keyword);
        if ($trimmed === '') {
            return $query;
        }

        $tokens = array_values(array_filter(preg_split('/[\s,]+/', $trimmed) ?: []));
        $primaryToken = $tokens[0] ?? $trimmed;

        $query->where(function (Builder $nameQuery) use ($primaryToken) {
            $prefix = $primaryToken . '%';
            $nameQuery
                ->where('last_name', 'like', $prefix)
                ->orWhere('first_name', 'like', $prefix);
        });

        foreach ($tokens as $token) {
            $like = '%' . $token . '%';
            $query->where(function (Builder $tokenQuery) use ($like) {
                $tokenQuery
                    ->where('first_name', 'like', $like)
                    ->orWhere('middle_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('suffix', 'like', $like);
            });
        }

        return $query;
    }

    private function normalizeHouseholdDate($value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
