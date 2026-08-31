@props(['invitation', 'token', 'quote', 'products', 'currency', 'locked' => false])
@php
    $quoteItems = collect($quote?->items ?? [])->keyBy('inquiry_item_id');
@endphp
<form method="post" action="{{ route('rfq.public.respond', ['token' => $token]) }}" id="rfq-pricing-form" class="ft-rfq-portal-stack">
    @csrf
    <section class="ft-rfq-portal-card ft-rfq-portal-section-card">
        <div class="ft-rfq-portal-card__header ft-rfq-card-header-with-select">
            <div>
                <h2>Product pricing</h2>
                <p>Enter unit pricing and minimum order quantity for each requested product.</p>
            </div>
            <label class="ft-rfq-inline-select">
                <span>Currency</span>
                <select name="currency" data-rfq-currency @disabled($locked)>
                    @foreach(['USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP', 'CNY' => 'CNY'] as $code => $label)
                        <option value="{{ $code }}" @selected(old('currency', $currency) === $code)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="ft-rfq-pricing-table-wrap">
            <table class="ft-rfq-pricing-table">
                <thead><tr><th>Product</th><th>Quantity</th><th>Unit price</th><th>MOQ</th><th>Subtotal</th></tr></thead>
                <tbody>
                @foreach($products as $product)
                    @php($item = $quoteItems->get($product['item_id']))
                    <tr data-rfq-price-row data-quantity="{{ (float) $product['quantity'] }}">
                        <td>
                            <div class="ft-rfq-table-product">
                                <x-rfq.public.product-thumb :product="$product" size="sm" />
                                <span><strong>{{ $product['name'] }}</strong><small>{{ $product['code'] ?: 'Product' }}</small></span>
                            </div>
                        </td>
                        <td>{{ number_format((float) $product['quantity'], fmod((float) $product['quantity'], 1.0) === 0.0 ? 0 : 2) }}</td>
                        <td><input type="number" name="prices[{{ $product['item_id'] }}]" value="{{ old('prices.'.$product['item_id'], $item?->unit_price) }}" min="0" step="0.0001" required data-rfq-price @disabled($locked)></td>
                        <td><input type="number" name="moqs[{{ $product['item_id'] }}]" value="{{ old('moqs.'.$product['item_id'], $item?->moq) }}" min="0" step="1" @disabled($locked)></td>
                        <td class="ft-rfq-subtotal-preview">Calculated on review</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="ft-rfq-portal-card ft-rfq-portal-section-card">
        <div class="ft-rfq-portal-card__header"><div><h2>Pricing &amp; commercial terms</h2><p>Add setup, sample and other commercial costs.</p></div></div>
        <div class="ft-rfq-form-grid is-four">
            <label><span>Tooling / setup</span><input type="number" name="tooling_cost" value="{{ old('tooling_cost', $quote?->tooling_cost ?? 0) }}" min="0" step="0.01" @disabled($locked)></label>
            <label><span>Sample cost</span><input type="number" name="sample_cost" value="{{ old('sample_cost', $quote?->sample_cost ?? 0) }}" min="0" step="0.01" @disabled($locked)></label>
            <label><span>Freight / other costs</span><input type="number" name="freight" value="{{ old('freight', $quote?->freight ?? 0) }}" min="0" step="0.01" @disabled($locked)></label>
            <label><span>Discount</span><input type="number" name="discount" value="{{ old('discount', $quote?->discount ?? 0) }}" min="0" step="0.01" @disabled($locked)></label>
            <label><span>Tax</span><select name="tax_status" @disabled($locked)><option value="excluded" @selected(old('tax_status', $quote?->tax_status ?? 'excluded') === 'excluded')>Excluded</option><option value="included" @selected(old('tax_status', $quote?->tax_status) === 'included')>Included</option></select></label>
            <label><span>Production lead time</span><div class="ft-rfq-input-suffix"><input type="number" name="lead_time_days" value="{{ old('lead_time_days', $quote?->lead_time_days) }}" min="0" @disabled($locked)><span>days</span></div></label>
            <label><span>Sample lead time</span><div class="ft-rfq-input-suffix"><input type="number" name="sample_lead_time_days" value="{{ old('sample_lead_time_days', $quote?->sample_lead_time_days) }}" min="0" @disabled($locked)><span>days</span></div></label>
            <label><span>Quote validity</span><div class="ft-rfq-input-suffix"><input type="number" name="validity_days" value="{{ old('validity_days', $quote?->validity_days ?? 30) }}" min="0" @disabled($locked)><span>days</span></div></label>
            <label><span>Incoterm</span><input type="text" name="incoterm" value="{{ old('incoterm', $quote?->incoterm) }}" placeholder="FOB" @disabled($locked)></label>
            <label><span>Shipping port</span><input type="text" name="shipping_port" value="{{ old('shipping_port', $quote?->shipping_port) }}" placeholder="Shanghai" @disabled($locked)></label>
            <label><span>Estimated delivery</span><input type="date" name="estimated_delivery_date" value="{{ old('estimated_delivery_date', $quote?->estimated_delivery_date?->format('Y-m-d')) }}" @disabled($locked)></label>
            <label><span>Specification compliance</span><select name="specification_compliance" @disabled($locked)><option value="">Select</option><option value="yes" @selected(old('specification_compliance', $quote?->specification_compliance) === 'yes')>Yes, fully compliant</option><option value="partial" @selected(old('specification_compliance', $quote?->specification_compliance) === 'partial')>Partially compliant</option><option value="no" @selected(old('specification_compliance', $quote?->specification_compliance) === 'no')>Not compliant</option></select></label>
            <label class="is-full"><span>Pricing / production notes</span><textarea name="notes" rows="3" @disabled($locked) placeholder="Add packaging, sample availability or production notes.">{{ old('notes', $quote?->notes) }}</textarea></label>
        </div>
        <div class="ft-rfq-live-total-row"><span>Estimated quoted value</span><strong data-rfq-live-total>{{ $currency }} {{ number_format((float) ($quote?->submitted_total ?? 0), 2) }}</strong></div>
    </section>

    @unless($locked)
        <div class="ft-rfq-portal-bottom-actions">
            <a class="ft-rfq-btn is-secondary" href="{{ route('rfq.public.show', ['token' => $token, 'step' => 'details']) }}"><x-rfq.public.icon name="arrow-left" /> Back to product details</a>
            <div>
                <button type="submit" class="ft-rfq-btn is-secondary" name="action" value="save_pricing"><x-rfq.public.icon name="save" /> Save draft</button>
                <button type="submit" class="ft-rfq-btn is-primary" name="action" value="continue_documents">Continue to documents <x-rfq.public.icon name="chevron-right" /></button>
            </div>
        </div>
    @endunless
</form>
