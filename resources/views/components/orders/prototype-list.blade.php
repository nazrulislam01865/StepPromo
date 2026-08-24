@props([
    'jobs',
    'rows' => [],
    'stages' => collect(),
    'selectedStage' => null,
    'stageQuickFilters' => ['all' => 'All'],
    'searchFilter' => '',
    'clientFilter' => '',
    'ownerFilter' => '',
    'phaseFilter' => '',
    'dateFrom' => '',
    'dateTo' => '',
    'stageQuick' => 'all',
    'stageSupplier' => '',
    'stageAssignee' => '',
    'stageUrgency' => '',
    'stageCarrier' => '',
    'stageClient' => '',
    'clientFilterOptions' => collect(),
    'ownerFilterOptions' => collect(),
    'stageAssigneeOptions' => collect(),
    'supplierFilterOptions' => collect(),
    'shipmentUrgencyOptions' => collect(),
    'importFilterId' => 0,
    'importFilterLabel' => '',
])
@php
    $sequence = (int) data_get($selectedStage, 'sequence', 0);
    $stageName = (string) data_get($selectedStage, 'name', '');
    $currentPage = $jobs->currentPage();
    $lastPage = $jobs->lastPage();
    $pageNumbers = collect(range(1, max(1, $lastPage)))
        ->filter(fn ($page) => $lastPage <= 5 || $page === 1 || $page === $lastPage || abs($page - $currentPage) <= 1)
        ->values();
    $formatDate = static fn ($date) => filled($date) ? \Carbon\CarbonImmutable::parse($date)->format('M j, Y') : '—';
    $money = static fn ($value) => '$'.number_format((float) $value, 2);
    $stageTextColor = static function (?string $color): string {
        $hex = trim((string) $color);

        if (! preg_match('/^#([0-9a-fA-F]{6})$/', $hex, $matches)) {
            return '#ffffff';
        }

        $rgb = $matches[1];
        $red = hexdec(substr($rgb, 0, 2));
        $green = hexdec(substr($rgb, 2, 2));
        $blue = hexdec(substr($rgb, 4, 2));
        $luminance = (0.299 * $red) + (0.587 * $green) + (0.114 * $blue);

        return $luminance >= 170 ? '#16324f' : '#ffffff';
    };
    $headers = match ($sequence) {
        1 => ['Order','Client','Product / supplier','Purchase order','Owner / required date','Next action'],
        2 => ['Order','Product / supplier','Artwork','Client / sample','Assignee / due','Next action'],
        3 => ['Order','Product / supplier','Quantity','Production','Issue','Owner / required date','Next action'],
        4 => ['Order','Product / supplier','Inspection','QC status','Issue','Assignee / due','Next action'],
        5 => ['Order','Client','Urgency','Label / carrier','Tracking','Owner / delivery','Next action'],
        6 => ['Order','Client','Invoice','Amount','Invoice status','Owner / due','Next action'],
        7 => ['Order','Client','Invoice','Paid / balance','Payment status','Owner / due','Next action'],
        default => ['Order','Client & product','Current stage','Status','Owner / delivery','Progress'],
    };
    $selectedStageFiltersActive = filled($stageSupplier) || filled($stageAssignee) || filled($stageUrgency) || filled($stageCarrier) || filled($stageClient) || $stageQuick !== 'all';
    // CHANGE 2026-08-24: prototype checkbox colors for the selected phase.
    $stageQuickMeta = \App\Services\OrderListPrototypeService::quickFilterMeta($sequence);
@endphp

<div class="ft-order-list-v5">
    @include('components.orders.list.header-and-stages')
    @include('components.orders.list.filters')
    @include('components.orders.list.table')
    @include('components.orders.list.pagination')
</div>
