@props(['job', 'detailTab', 'canViewFinance' => false, 'canCreateFinance' => false])
<nav class="page-tabs ft-order-prototype-tabs" aria-label="Order detail tabs">
    <button type="button" class="page-tab {{ $detailTab === 'overview' ? 'active' : '' }}" wire:click="setDetailTab('overview')">Overview</button>
    <button type="button" class="page-tab {{ $detailTab === 'inquiry' ? 'active' : '' }}" wire:click="setDetailTab('inquiry')">Inquiry &nbsp;<span class="status-pill">{{ $job->source_inquiry_id ? 1 : 0 }}</span></button>
    @if($canViewFinance)
        <button type="button" class="page-tab {{ $detailTab === 'finance' ? 'active' : '' }}" wire:click="setDetailTab('finance')">Invoices &amp; Payments</button>
    @endif
</nav>
@if($detailTab === 'finance' && $canCreateFinance)
    <div class="finance-tab-actions">
        <button type="button" class="btn" wire:click="openRecordPayment">Record Payment</button>
        <button type="button" class="btn primary" wire:click="openCreateInvoice">＋ Create Invoice</button>
    </div>
@endif
