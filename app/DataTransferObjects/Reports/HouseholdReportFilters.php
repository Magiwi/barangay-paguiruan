<?php

namespace App\DataTransferObjects\Reports;

class HouseholdReportFilters
{
    public function __construct(
        public ?int $purokId,
        public string $sort,
        public string $direction,
        public ?int $selectedHeadId,
        public string $headQuery,
        public ?string $residentType,
        public ?string $statusFilter,
        public ?int $membersMin,
        public ?int $membersMax,
        public ?string $createdFrom,
        public ?string $createdTo
    ) {
    }

    public function toArray(): array
    {
        return [
            'purokId' => $this->purokId,
            'sort' => $this->sort,
            'direction' => $this->direction,
            'selectedHeadId' => $this->selectedHeadId,
            'headQuery' => $this->headQuery,
            'residentType' => $this->residentType,
            'statusFilter' => $this->statusFilter,
            'membersMin' => $this->membersMin,
            'membersMax' => $this->membersMax,
            'createdFrom' => $this->createdFrom,
            'createdTo' => $this->createdTo,
        ];
    }
}
