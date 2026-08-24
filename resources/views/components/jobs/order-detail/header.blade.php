@props(['job', 'context' => [], 'shipmentUrgencyOptions' => collect()])
@php
    $team = collect($context['team'] ?? []);
    $canEditJob = (bool) ($context['canEditJob'] ?? false);
    $canChangeOwner = (bool) ($context['canChangeOwner'] ?? false);
    $canCancel = (bool) ($context['canCancel'] ?? false);
    $attentionLocked = (bool) ($context['attentionLocked'] ?? false);
    $flagged = (bool) ($context['flagged'] ?? false);
    $flagReason = trim((string) ($context['flagReason'] ?? ''));
    $isCancelled = strcasecmp((string) $job->status, 'Cancelled') === 0;
    $stageName = $isCancelled ? ($job->phase?->name ?: 'Cancelled') : ($job->phase?->name ?: 'New Order');
@endphp
<section class="detail-header ft-order-prototype-header">
    <div class="breadcrumbs ft-order-prototype-breadcrumb">
        Orders &nbsp;/&nbsp;
        <b>{{ $job->displayOrderNumber() }}</b>
        <button type="button" class="copy-order-code" title="Copy Order ID" aria-label="Copy {{ $job->displayOrderNumber() }}" data-copy-value="{{ $job->displayOrderNumber() }}">▣</button>
    </div>

    <div class="detail-title-row ft-order-prototype-title-row">
        <div class="detail-heading" style="flex:1">
            <h1 class="detail-title ft-order-prototype-title">{{ $job->title }}</h1>

            <div class="meta-line ft-order-prototype-meta">
                <span>♙ &nbsp;Client <b>{{ $job->client?->name ?: '—' }}</b></span>
                <span class="meta-sep">•</span>
                <span>▯ &nbsp;Reference <b>{{ $job->order_number ?: '—' }}</b></span>
                <span class="meta-sep">•</span>
                <span>♙ &nbsp;Created by <b>{{ $job->creator?->name ?: 'System' }}</b></span>
                <span class="meta-sep">•</span>
                <span>▣ &nbsp;Created <b>{{ $job->created_at ? \App\Support\UserLocalTime::format($job->created_at, 'M j, Y \\a\\t g:i A') : '—' }}</b></span>
            </div>

            <div class="pills ft-order-prototype-pills">
                <span class="pill {{ strtolower((string) $job->health) === 'on track' || blank($job->health) ? 'green' : 'amber' }}">{{ $job->health ?: 'On Track' }}</span>
                <span class="pill purple" id="stagePill" title="The workflow stage containing the current required task.">New Order</span>
                @if($isCancelled)<span class="cancelled-badge">⊘ Cancelled</span>@endif
            </div>

            <div class="order-commandbar ft-order-commandbar">
                <x-jobs.order-detail.shipment-urgency-inline
                    :job="$job"
                    :can-edit-job="$canEditJob"
                    :shipment-urgency-options="$shipmentUrgencyOptions"
                    :context="$context"
                    variant="header"
                />

                <div class="header-owner-control ft-inline-edit-shell"
                    x-data="window.FlowTrack.ui.inlineEdit({ key: @js('job-'.$job->id.'-header-owner'), label: 'Order owner', value: @js($job->owner_id ?? ''), display: @js($job->owner?->name ?? 'Unassigned'), avatarUrl: @js($job->owner?->profileImageUrl() ?? '') })"
                    x-on:click.outside="if(editing) cancelEdit()"
                    x-on:ft-inline-remote-cancel.stop="cancelEdit()"
                    x-on:ft-inline-remote-selected.stop="commit(String($event.detail?.value ?? ''), String($event.detail?.label ?? 'Unassigned'), () => $wire.updateJobOwner({{ $job->id }}, draftValue), { avatarUrl: String($event.detail?.avatarUrl ?? '') })"
                >
                    @if($canChangeOwner)
                        <button
                            x-ref="headerOwnerAnchor"
                            type="button"
                            class="assignee-stage-chip ft-order-owner-chip-trigger"
                            :class="{ 'is-open': editing }"
                            x-on:click.stop="openRemotePicker($refs.headerOwnerAnchor)"
                            title="Edit order owner"
                            aria-label="Edit order owner"
                        >
                            <x-ui.inline-live-avatar :size="22" />
                            <span x-text="display">{{ $job->owner?->name ?: 'Unassigned' }}</span>
                            <span class="ft-order-inline-trigger-icon" aria-hidden="true">✎</span>
                        </button>
                        <div class="header-owner-picker" x-cloak x-show="editing">
                            <x-ui.inline-remote-user :value="$job->owner_id ?? ''" :selected-label="$job->owner?->name ?? 'Unassigned'" context="job-owner" parent-type="job" :parent-id="$job->id" search-placeholder="Search owner..." variant="compact" :menu-width="320" external-trigger />
                        </div>
                        <x-ui.inline-save-state compact />
                    @else
                        <span class="assignee-stage-chip"><x-ui.inline-live-avatar :size="22" /><span x-text="display">{{ $job->owner?->name ?: 'Unassigned' }}</span></span>
                    @endif
                </div>

                <span class="command-spacer"></span>
                <button type="button" class="btn small flag-btn {{ $flagged ? 'flagged' : '' }}" wire:click="openOrderAttentionReason" @disabled($attentionLocked) title="{{ $flagReason ?: 'Flag this order so it remains visibly marked for attention across every stage.' }}">⚑ {{ $flagged ? 'Flagged' : 'Flag order' }}</button>
                <button type="button" class="btn danger small" wire:click="openOrderCancelModal" @disabled(!$canCancel) title="{{ $canCancel ? 'Cancel this order. Cancellation is available only through the QC stage.' : 'Cancellation is available only through the QC stage.' }}">⊘ Cancel order</button>
            </div>
        </div>

        <div class="people ft-order-team-stack" title="Team members currently involved in this order">
            @foreach($team->take(4) as $member)
                @php($initials = collect(preg_split('/\s+/', trim((string) $member->name)))->filter()->map(fn($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode(''))
                <i title="{{ $member->name }}">{{ $initials ?: '—' }}</i>
            @endforeach
            @if($team->count() > 4)<i>+{{ $team->count() - 4 }}</i>@endif
        </div>
    </div>
</section>

@if($flagged)
    <div class="state-banner flag show ft-order-state-banner"><span>⚑</span><div><b>Flagged for attention</b><p>{{ $flagReason ?: 'This order requires attention.' }}</p></div></div>
@endif
@if($isCancelled)
    <div class="state-banner cancelled show ft-order-state-banner"><span>⊘</span><div><b>Order cancelled</b><p>{{ $job->cancellation_reason ?: 'Workflow progression is blocked.' }}@if($job->cancelledBy) · {{ $job->cancelledBy->name }}@endif</p></div></div>
@endif
