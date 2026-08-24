@php
    $orderId = (int) data_get($row, 'order_id', 0);
    $taskId = (int) data_get($row, 'next_task_id', 0);
    $label = (string) data_get($row, 'next_action', 'Open order');
@endphp

{{-- CHANGE 2026-08-24:
     Phase-wise Next Action now executes the same workflow action used on the
     Order Details page. Only rows without an actionable task keep the detail
     fallback link. --}}
@if($orderId > 0 && $taskId > 0)
    <button
        type="button"
        class="stage-action stage-action-button"
        wire:click="openListWorkflowAction({{ $orderId }}, {{ $taskId }})"
        wire:loading.attr="disabled"
        x-on:click.stop
    >
        {{ $label }}
    </button>
    <span class="stage-table-note">Perform action here</span>
@else
    <a class="stage-action" href="{{ $detailUrl }}" wire:navigate x-on:click.stop>{{ $label }}</a>
    <span class="stage-table-note">Open order to continue</span>
@endif
