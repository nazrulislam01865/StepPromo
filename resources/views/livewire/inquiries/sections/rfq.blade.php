@php
    $invitedCount = (int) ($inquiryRfqSummary['invited'] ?? 0);
    $submittedCount = (int) ($inquiryRfqSummary['submitted'] ?? 0);
    $rfqInitials = static function (?string $name): string {
        $parts = preg_split('/\s+/u', trim((string) $name)) ?: [];
        return strtoupper(substr(implode('', array_map(fn ($part) => mb_substr($part, 0, 1), $parts)), 0, 2)) ?: '—';
    };
    $rfqStatusLabel = static function ($invitation): string {
        if ($invitation->awarded_at) return 'Selected';
        if ($invitation->rejected_at) return 'Not selected';
        if ($invitation->quote_status === 'submitted') return 'Submitted';
        return 'Pending';
    };
@endphp
<div class="ft-rfq-pane" wire:key="inquiry-rfq-pane-{{ $selectedInquiry->id }}">
    <section class="ft-rfq-card">
        <header class="ft-rfq-card-head">
            <div>
                <h2>Request for quotation</h2>
                <p>Invite suppliers to express interest and submit a quotation through a secure email link.</p>
            </div>
            <span class="ft-rfq-count-pill">{{ number_format($invitedCount) }} {{ \Illuminate\Support\Str::plural('supplier', $invitedCount) }} invited</span>
        </header>

        <div class="ft-rfq-search-area">
            <label for="rfq-supplier-search">Find and invite suppliers</label>
            <div class="ft-rfq-search-shell">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4"></path></svg>
                <input id="rfq-supplier-search" type="search" wire:model.live.debounce.300ms="rfqSupplierSearch" placeholder="Search supplier name, category or email" autocomplete="off">
            </div>
            @error('rfqSupplierSearch')<p class="ft-rfq-error">{{ $message }}</p>@enderror

            <div class="ft-rfq-candidates">
                <div class="ft-rfq-candidate-summary">
                    <span>{{ number_format($rfqSupplierCandidates->count()) }} {{ \Illuminate\Support\Str::plural('supplier', $rfqSupplierCandidates->count()) }} from Supplier list</span>
                    <span>Invite active suppliers with a valid email</span>
                </div>
                @forelse($rfqSupplierCandidates as $candidate)
                    @php
                        $candidateInvitable = (bool) ($candidate['invitable'] ?? false);
                        $candidateUnavailableReason = trim((string) ($candidate['unavailable_reason'] ?? ''));
                        $candidateEmail = trim((string) ($candidate['email'] ?? ''));
                    @endphp
                    <div class="ft-rfq-candidate-row {{ $candidateInvitable ? '' : 'is-unavailable' }}" wire:key="rfq-candidate-{{ $candidate['id'] }}">
                        <div class="ft-rfq-supplier-avatar">{{ $rfqInitials($candidate['name']) }}</div>
                        <div class="ft-rfq-supplier-copy">
                            <strong>{{ $candidate['name'] }}</strong>
                            <span>{{ $candidate['category'] }} · {{ $candidateEmail !== '' ? $candidateEmail : 'No email configured' }}</span>
                        </div>
                        @if($candidateInvitable && $canManageInquiryRfq)
                            <button type="button" class="ft-rfq-primary-small" wire:click="inviteRfqSupplier({{ $candidate['id'] }})" wire:loading.attr="disabled" wire:target="inviteRfqSupplier({{ $candidate['id'] }})">
                                <span wire:loading.remove wire:target="inviteRfqSupplier({{ $candidate['id'] }})">Invite</span>
                                <span wire:loading wire:target="inviteRfqSupplier({{ $candidate['id'] }})">Sending…</span>
                            </button>
                        @elseif(! $candidateInvitable)
                            <span class="ft-rfq-candidate-unavailable" title="Update this supplier in Master Data before sending an RFQ">{{ $candidateUnavailableReason !== '' ? $candidateUnavailableReason : 'Unavailable' }}</span>
                        @endif
                    </div>
                @empty
                    <div class="ft-rfq-empty-candidate">{{ trim($rfqSupplierSearch) !== '' ? 'No suppliers from the Supplier list match your search.' : 'No uninvited suppliers remain in the Supplier list.' }}</div>
                @endforelse
            </div>
        </div>

        <div class="ft-rfq-table-wrap">
            <table class="ft-rfq-table">
                <thead><tr><th>Supplier</th><th>Invited</th><th>Email status</th><th>Expression of interest</th><th>Quotation</th><th></th></tr></thead>
                <tbody>
                    @foreach($rfqDefaultSuppliers as $defaultSupplier)
                        @php
                            $defaultInvitable = (bool) ($defaultSupplier['invitable'] ?? false);
                            $defaultEmail = trim((string) ($defaultSupplier['email'] ?? ''));
                            $defaultUnavailableReason = trim((string) ($defaultSupplier['unavailable_reason'] ?? ''));
                            $defaultProductCount = (int) ($defaultSupplier['product_count'] ?? 0);
                        @endphp
                        <tr class="ft-rfq-default-send-row" wire:key="rfq-default-send-{{ $defaultSupplier['id'] }}">
                            <td data-label="Supplier">
                                <div class="ft-rfq-table-supplier">
                                    <span class="ft-rfq-supplier-avatar">{{ $rfqInitials($defaultSupplier['name']) }}</span>
                                    <div>
                                        <strong>{{ $defaultSupplier['name'] }}</strong>
                                        <span>Default supplier{{ $defaultProductCount > 1 ? ' · '.number_format($defaultProductCount).' inquiry products' : '' }} · {{ $defaultEmail !== '' ? $defaultEmail : 'No email configured' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Invited">Not sent</td>
                            <td data-label="Email status">
                                <span class="ft-rfq-status-pill {{ $defaultInvitable ? 'is-blue' : 'is-neutral' }}">{{ $defaultInvitable ? 'Ready to send' : ($defaultUnavailableReason !== '' ? $defaultUnavailableReason : 'Unavailable') }}</span>
                            </td>
                            <td data-label="Expression of interest"><span class="ft-rfq-status-pill is-neutral">—</span></td>
                            <td data-label="Quotation"><span class="ft-rfq-status-pill is-neutral">—</span></td>
                            <td class="ft-rfq-table-action" data-label="Action">
                                @if($defaultInvitable && $canManageInquiryRfq)
                                    <button type="button" class="ft-rfq-primary-small" wire:click="inviteRfqSupplier({{ $defaultSupplier['id'] }})" wire:loading.attr="disabled" wire:target="inviteRfqSupplier({{ $defaultSupplier['id'] }})">
                                        <span wire:loading.remove wire:target="inviteRfqSupplier({{ $defaultSupplier['id'] }})">Send</span>
                                        <span wire:loading wire:target="inviteRfqSupplier({{ $defaultSupplier['id'] }})">Sending…</span>
                                    </button>
                                @elseif(! $defaultInvitable)
                                    <span class="ft-rfq-candidate-unavailable">{{ $defaultUnavailableReason !== '' ? $defaultUnavailableReason : 'Unavailable' }}</span>
                                @else
                                    <span>—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    @foreach($rfqInvitations as $invitation)
                        @php
                            $interest = $invitation->interest_status === 'interested' ? 'Interested' : ($invitation->interest_status === 'declined' ? 'Declined' : 'Pending');
                            $quotation = $rfqStatusLabel($invitation);
                        @endphp
                        <tr wire:key="rfq-invitation-{{ $invitation->id }}">
                            <td data-label="Supplier">
                                <div class="ft-rfq-table-supplier"><span class="ft-rfq-supplier-avatar">{{ $rfqInitials($invitation->supplier?->name) }}</span><div><strong>{{ $invitation->supplier?->name ?: 'Supplier' }}</strong><span>{{ $invitation->supplierEmail() ?: 'No email' }}</span></div></div>
                            </td>
                            <td data-label="Invited">{{ $invitation->invited_at ? \App\Support\UserLocalTime::format($invitation->invited_at, 'M j, Y') : '—' }}</td>
                            <td data-label="Email status"><span class="ft-rfq-status-pill {{ $invitation->email_status === 'Delivered' ? 'is-green' : ($invitation->email_status === 'Failed' ? 'is-red' : 'is-blue') }}">{{ $invitation->email_status }}</span></td>
                            <td data-label="Expression of interest"><span class="ft-rfq-status-pill {{ $interest === 'Interested' ? 'is-green' : ($interest === 'Declined' ? 'is-red' : 'is-neutral') }}">{{ $interest }}</span></td>
                            <td data-label="Quotation"><span class="ft-rfq-status-pill {{ in_array($quotation, ['Submitted','Selected'], true) ? 'is-green' : ($quotation === 'Not selected' ? 'is-neutral' : 'is-blue') }}">{{ $quotation }}</span></td>
                            <td class="ft-rfq-table-action" data-label="Action">
                                @if($invitation->quote_status === 'submitted')
                                    <button type="button" class="ft-rfq-link" wire:click="setDetailTab('comparison')">View response</button>
                                @else
                                    <span>—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    @if($rfqDefaultSuppliers->isEmpty() && $rfqInvitations->isEmpty())
                        <tr><td colspan="6"><div class="ft-rfq-table-empty">No suppliers are ready to send yet.</div></td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </section>

    <div class="ft-rfq-info-note"><strong>RFQ participation does not assign a supplier to the product.</strong> Suppliers are only candidates until the company compares quotations and awards one.</div>
</div>
