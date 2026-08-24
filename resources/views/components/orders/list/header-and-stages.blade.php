    @if(session('success'))
        <div class="ft-order-list-flash" role="status">{{ session('success') }}</div>
    @endif

    <header class="list-head">
        <div>
            <div class="breadcrumbs">Order / Orders</div>
            <h1>Orders</h1>
            <p class="sub">Manage active orders, see the exact workflow stage, and open the next required action.</p>
        </div>
        <div class="top-actions">
            @if(auth()->user()->canAccess('jobs.create'))
                <a class="btn" href="{{ route('orders.bulk-import') }}">⇧ Bulk order</a>
            @endif
            @if(auth()->user()->canModule('jobs', 'create'))
                <a class="btn primary" href="{{ route('jobs.index', ['create' => 1]) }}" wire:navigate>＋ Create order</a>
            @endif
        </div>
    </header>

    <section class="list-card stage-overview-card" aria-label="Orders by workflow stage">
        <div class="list-section-head">
            <div>
                <h2>Orders by workflow stage</h2>
                <p>Click a stage to filter the orders below on this page.</p>
            </div>
            <button class="btn small primary" type="button" wire:click="selectStage(null)">Show all</button>
        </div>
        <div class="list-stage-strip">
            @foreach($stages as $stage)
                @php
                    $isSelectedStage = (string) $phaseFilter === (string) data_get($stage, 'id');
                @endphp
                <button
                    type="button"
                    class="list-stage-card {{ $isSelectedStage ? 'active' : '' }}"
                    style="--stage:{{ data_get($stage, 'color', '#2d72d9') }};--stage-text:{{ $stageTextColor(data_get($stage, 'color', '#2d72d9')) }}"
                    wire:click="selectStage({{ (int) data_get($stage, 'id') }})"
                    aria-pressed="{{ $isSelectedStage ? 'true' : 'false' }}"
                >
                    @if($isSelectedStage)
                        <span class="list-stage-selected-badge" aria-hidden="true"><i>✓</i> Selected</span>
                    @endif
                    <span class="list-stage-kicker">Stage {{ (int) data_get($stage, 'sequence') }}</span>
                    <b title="{{ data_get($stage, 'name') }}">{{ data_get($stage, 'short_name') ?: data_get($stage, 'name') }}</b>
                    <span class="list-stage-count"><em>Current orders</em><strong>{{ number_format((int) data_get($stage, 'count', 0)) }}</strong></span>
                </button>
            @endforeach
        </div>
    </section>

