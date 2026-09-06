<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Masterdata\Equipment\Models\Equipment;

final class FetchEquipmentListService
{
    public function __construct(
        private readonly AppendEquipmentOperatingTimeService $appendEquipmentOperatingTimeService
    ) {}

    /**
     * Build query, fetch equipment records, and append operating times.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Equipment>|Collection<int, Equipment>
     */
    public function fetch(array $filters = []): LengthAwarePaginator|Collection
    {
        $isAll = (bool) ($filters['all'] ?? false);

        $query = Equipment::query()
            ->with([
                'equipmentCategory',
                'equipmentParameters.unit',
                'equipmentErrors',
                'equipmentState',
                'equipmentImages',
            ])
            ->withChecklistSessionsAndDetails()
            ->filter($filters);

        if ($isAll) {
            $items = $query->get();
            $this->appendEquipmentOperatingTimeService->append($items);
            return $items;
        }

        $paginator = $query->paginate((int) ($filters['per_page'] ?? 15));
        $this->appendEquipmentOperatingTimeService->append($paginator->getCollection());
        return $paginator;
    }
}
