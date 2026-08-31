@props(['invitation', 'token', 'quote', 'products', 'contact', 'rfqReference', 'locked' => false])
@php
    $inquiry = $invitation->inquiry;
    $firstProduct = collect($products)->first();
@endphp
<form method="post" action="{{ route('rfq.public.respond', ['token' => $token]) }}" id="rfq-details-form" class="ft-rfq-portal-stack">
    @csrf
    <section class="ft-rfq-portal-card ft-rfq-portal-section-card">
        <div class="ft-rfq-portal-card__header">
            <div>
                <h2>Supplier and RFQ details</h2>
                <p>Confirm the contact information for this quotation request.</p>
            </div>
        </div>

        <div class="ft-rfq-details-grid">
            <div class="ft-rfq-readonly-field"><small>Supplier company</small><strong>{{ $invitation->supplier?->name ?: '—' }}</strong></div>
            <div class="ft-rfq-readonly-field"><small>Inquiry reference</small><strong>{{ $inquiry->inquiry_number }}</strong></div>
            <div class="ft-rfq-readonly-field"><small>RFQ reference</small><strong>{{ $rfqReference }}</strong></div>
        </div>

        <div class="ft-rfq-form-grid is-three">
            <label>
                <span>Contact person *</span>
                <input type="text" name="supplier_contact_name" value="{{ old('supplier_contact_name', $contact['name'] ?? '') }}" required @disabled($locked)>
            </label>
            <label>
                <span>Email *</span>
                <input type="email" name="supplier_contact_email" value="{{ old('supplier_contact_email', $contact['email'] ?? '') }}" required @disabled($locked)>
            </label>
            <label>
                <span>Phone</span>
                <input type="text" name="supplier_contact_phone" value="{{ old('supplier_contact_phone', $contact['phone'] ?? '') }}" @disabled($locked)>
            </label>
        </div>
    </section>

    <section class="ft-rfq-portal-card ft-rfq-portal-section-card">
        <div class="ft-rfq-portal-card__header">
            <div>
                <h2>Requested product</h2>
                <p>Review the product and requested quantity before entering pricing.</p>
            </div>
        </div>
        <div class="ft-rfq-product-list">
            @foreach($products as $product)
                <article class="ft-rfq-product-line">
                    <x-rfq.public.product-thumb :product="$product" size="lg" />
                    <div class="ft-rfq-product-line__copy">
                        <strong>{{ $product['name'] }}</strong>
                        <span>{{ $product['code'] ?: 'Product' }} @if($product['category']) · {{ $product['category'] }} @endif</span>
                    </div>
                    <div class="ft-rfq-product-line__quantity">
                        <small>Requested quantity</small>
                        <strong>{{ number_format((float) $product['quantity'], fmod((float) $product['quantity'], 1.0) === 0.0 ? 0 : 2) }} {{ $product['unit'] }}</strong>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    @unless($locked)
        <div class="ft-rfq-portal-bottom-actions">
            <span></span>
            <div>
                <button type="submit" class="ft-rfq-btn is-secondary" name="action" value="save_details"><x-rfq.public.icon name="save" /> Save draft</button>
                <button type="submit" class="ft-rfq-btn is-primary" name="action" value="continue_pricing">Continue to pricing <x-rfq.public.icon name="chevron-right" /></button>
            </div>
        </div>
    @endunless
</form>
