    <section class="list-card orders-table-card" aria-label="Orders">
        <div class="filter-toolbar">
            <label class="search-box">
                <span>⌕</span>
                <input type="search" autocomplete="off" placeholder="Search order, reference, client or product" wire:model.live.debounce.700ms="search">
            </label>

            <select wire:model.live="client" aria-label="Client filter">
                <option value="">All clients</option>
                @foreach($clientFilterOptions as $option)
                    <option value="{{ data_get($option, 'id') }}">{{ data_get($option, 'label') }}</option>
                @endforeach
            </select>

            <select wire:model.live="owner" aria-label="Owner filter">
                <option value="">All owners</option>
                @foreach($ownerFilterOptions as $option)
                    <option value="{{ data_get($option, 'id') }}">{{ data_get($option, 'label') }}</option>
                @endforeach
            </select>

            <select wire:model.live="phase" aria-label="Workflow stage filter">
                <option value="">All stages</option>
                @foreach($stages as $stage)
                    <option value="{{ data_get($stage, 'id') }}">{{ data_get($stage, 'name') }}</option>
                @endforeach
            </select>

            <x-ui.date-range
                class="ft-order-list-date-range"
                from-property="dateFrom"
                to-property="dateTo"
                :from-value="$dateFrom"
                :to-value="$dateTo"
                label="Created date range"
                from-label="From"
                to-label="To"
            />
            <button class="btn" type="button" wire:click="clearFilters">Clear</button>
        </div>

        @if($selectedStage)
            <div class="stage-inline-controls">
                <span class="stage-inline-label">{{ $stageName }}</span>
                <div class="stage-inline-quick" role="group" aria-label="{{ $stageName }} status filters">
                    @foreach($stageQuickFilters as $key => $label)
                        @php
                            $quickColor = (string) data_get($stageQuickMeta, $key.'.color', '#0F8F7C');
                        @endphp
                        <button
                            type="button"
                            class="stage-inline-chip {{ $stageQuick === $key ? 'active' : '' }}"
                            style="--quick-color:{{ $quickColor }}"
                            wire:click="setStageQuick('{{ $key }}')"
                            aria-pressed="{{ $stageQuick === $key ? 'true' : 'false' }}"
                        >
                            <span class="stage-inline-check" aria-hidden="true">✓</span>
                            <span>{{ $label }}</span>
                        </button>
                    @endforeach
                </div>
                <span class="stage-view-note">Row colors match {{ strtolower($stageName) }} status</span>
                <div class="stage-inline-selects">
                    @if(in_array($sequence, [1,2,3,4], true))
                        <select wire:model.live="stageSupplier" aria-label="Supplier stage filter">
                            <option value="">All supplier</option>
                            @foreach($supplierFilterOptions as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="stageAssignee" aria-label="Stage assignee filter">
                            <option value="">{{ $sequence === 1 ? 'All order owner' : 'All '.strtolower($stageName).' assignee' }}</option>
                            @foreach($stageAssigneeOptions as $option)
                                <option value="{{ data_get($option, 'id') }}">{{ data_get($option, 'label') }}</option>
                            @endforeach
                        </select>
                    @elseif($sequence === 5)
                        <select wire:model.live="stageUrgency" aria-label="Shipping urgency filter">
                            <option value="">All shipping urgency</option>
                            @foreach($shipmentUrgencyOptions as $option)
                                <option value="{{ $option->id }}">{{ $option->name }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="stageCarrier" aria-label="Carrier filter">
                            <option value="">All carrier</option>
                            <option>UPS</option><option>FedEx</option><option>DHL</option><option>Other</option>
                        </select>
                    @elseif(in_array($sequence, [6,7], true))
                        <select wire:model.live="stageClient" aria-label="Stage client filter">
                            <option value="">All clients</option>
                            @foreach($clientFilterOptions as $option)
                                <option value="{{ data_get($option, 'id') }}">{{ data_get($option, 'label') }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="stageAssignee" aria-label="Finance owner filter">
                            <option value="">All finance owner</option>
                            @foreach($stageAssigneeOptions as $option)
                                <option value="{{ data_get($option, 'id') }}">{{ data_get($option, 'label') }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
        @endif

        @if($importFilterId)
            <div class="import-filter-line"><b>Imported batch:</b> {{ $importFilterLabel ?: '#'.$importFilterId }}</div>
        @endif

        <div class="active-filter-line">
            <span>{{ number_format($jobs->total()) }} {{ \Illuminate\Support\Str::plural('order', $jobs->total()) }}</span>
            <span>{{ $selectedStage ? $stageName.' filter · same Orders page' : 'Showing all workflow stages' }}</span>
        </div>

