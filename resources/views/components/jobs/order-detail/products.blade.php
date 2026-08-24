@props([
    'job', 'context' => [], 'showAddJobProductForm' => false, 'jobProductSearch' => '',
    'jobProductSearchResults' => collect(), 'jobProductResultTotal' => 0,
    'jobProductSelectedProduct' => null, 'jobProductCategory' => '', 'jobProductSupplierId' => null,
    'jobProductSupplierLabel' => '', 'jobProductSupplierLocked' => false,
    'showEditOrderProductModal' => false, 'editOrderProductItemId' => null, 'editOrderProductName' => '', 'editOrderProductCode' => '',
    'editOrderProductSupplierId' => null, 'editOrderProductSupplierLabel' => '', 'editOrderProductQuantity' => '1',
    'editOrderProductUnitPrice' => '0.00', 'editOrderProductNotes' => '',
])
@php
    // Presentation only. JobService eager-loads the product, supplier and audit
    // relationships before this component is rendered, so no relationship
    // query is issued from the view.
    $items = \App\Support\JobDetailPresenter::products($job);
    $activeItems = $items->filter(fn ($item) => !($item->is_removed ?? false))->values();
    $removedItems = $items->filter(fn ($item) => (bool) ($item->is_removed ?? false))->values();
    $canView = (bool) ($context['canViewProducts'] ?? false);
    $canEdit = (bool) ($context['canEditProducts'] ?? false);
    $canCreate = (bool) ($context['canCreateProducts'] ?? false);
    $canDelete = (bool) ($context['canDeleteProducts'] ?? false);
    $currency = strtoupper((string) ($job->currency ?: 'USD'));
    $symbol = match ($currency) { 'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'CNY', 'RMB' => '¥', default => $currency.' ' };
    $activeUnits = \App\Support\OrderDetailPresenter::totalActiveUnits($job);
    $activeValue = $activeItems->sum(fn ($item) => max(0, (int) ($item->quantity ?? 0)) * max(0, (float) ($item->unit_price ?? 0)));
@endphp

<section class="section-card ft-order-section-card ft-order-products-card ft-order-products-ux" id="productsSection">
    <header class="ft-order-products-ux-head">
        <div class="ft-order-products-ux-heading">
            <div>
                <h2>Products &amp; quantities</h2>
                <p>Products linked to this order, their supplier, quantity and commercial values.</p>
            </div>
            <div class="ft-order-products-ux-metrics" aria-label="Order product summary">
                <span><b>{{ $activeItems->count() }}</b> active {{ \Illuminate\Support\Str::plural('product', $activeItems->count()) }}</span>
                <span><b>{{ number_format($activeUnits) }}</b> total units</span>
                <span><b>{{ $symbol }}{{ number_format($activeValue, 2) }}</b> product value</span>
                @if($removedItems->isNotEmpty())
                    <span class="is-muted"><b>{{ $removedItems->count() }}</b> removed</span>
                @endif
            </div>
        </div>

    </header>

    @if(!$canView)
        <div class="ft-order-products-empty is-permission">
            <div class="ft-order-products-empty-icon" aria-hidden="true">⊘</div>
            <div><strong>Product details are unavailable</strong><span>You do not have permission to view catalog products on this order.</span></div>
        </div>
    @elseif($items->isEmpty() && !$showAddJobProductForm)
        <div class="ft-order-products-empty">
            <div class="ft-order-products-empty-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 12 12 20 4 12V4h8l8 8Z"/><circle cx="8.5" cy="8.5" r="1.2"/></svg>
            </div>
            <div class="ft-order-products-empty-copy">
                <strong>No products added yet</strong>
                <span>Add the first product, link its supplier, then enter quantity and unit price.</span>
            </div>
            @if($canCreate)
                <button type="button" class="btn primary" wire:click="openAddJobProductForm({{ $job->id }})">＋ Add first product</button>
            @endif
        </div>
    @elseif($items->isNotEmpty())
        <div class="ft-order-products-ux-table-wrap">
            <table class="ft-order-products-ux-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Supplier</th>
                        <th>Quantity</th>
                        <th>Unit price</th>
                        <th>Line total</th>
                        <th>Notes / updated</th>
                        <th class="ft-order-products-action-head">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        @php
                            $removed = (bool) ($item->is_removed ?? false);
                            $catalog = $item->catalogProduct;
                            $imageUrl = $catalog && method_exists($catalog, 'productImageUrl') ? $catalog->productImageUrl() : null;
                            $displayCode = $catalog && method_exists($catalog, 'productDisplayCode') ? $catalog->productDisplayCode() : '—';
                            $referenceCode = $catalog && method_exists($catalog, 'productReferenceCode') ? $catalog->productReferenceCode() : null;
                            $supplierName = \App\Support\OrderDetailPresenter::itemSupplierName($item, $job);
                            $categoryName = trim((string) ($item->category_name ?: ($catalog && method_exists($catalog, 'productClassificationPath') ? $catalog->productClassificationPath() : '')));
                            $quantity = max(0, (int) ($item->quantity ?? 0));
                            $unitPrice = max(0, (float) ($item->unit_price ?? 0));
                            $lineTotal = $quantity * $unitPrice;
                            $updatedBy = data_get($item, 'updatedBy.name') ?: data_get($item, 'removedBy.name') ?: $job->owner?->name ?: 'FlowTrack';
                            $updatedAt = $item->updated_at ? \App\Support\UserLocalTime::format($item->updated_at, 'M j, g:i A') : '—';
                        @endphp
                        <tr class="{{ $removed ? 'is-removed removed-product-row' : '' }}" wire:key="order-item-{{ $item->id ?? 'legacy' }}">
                            <td data-label="Product">
                                <div class="ft-order-products-product">
                                    <div class="ft-order-products-product-thumb">
                                        @if($imageUrl)<img src="{{ $imageUrl }}" alt="">@else
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M20 12 12 20 4 12V4h8l8 8Z"/><circle cx="8.5" cy="8.5" r="1.2"/></svg>
                                        @endif
                                    </div>
                                    <div class="ft-order-products-product-copy">
                                        <div class="ft-order-products-name-line">
                                            <strong>{{ $item->product_name ?: 'Unnamed product' }}</strong>
                                            @if($removed)<span class="ft-order-products-removed-chip">Removed</span>@endif
                                        </div>
                                        <span>Code {{ $displayCode }}@if($referenceCode) · Ref {{ $referenceCode }}@endif</span>
                                        @if($categoryName !== '')<small>{{ $categoryName }}</small>@endif
                                        @if($removed)<small class="is-removed-note">Retained for order history and audit.</small>@endif
                                    </div>
                                </div>
                            </td>
                            <td data-label="Supplier"><strong class="ft-order-products-supplier">{{ $supplierName }}</strong></td>
                            <td data-label="Quantity"><strong class="ft-order-products-number">{{ number_format($quantity) }}</strong><span class="ft-order-products-unit"> units</span></td>
                            <td data-label="Unit price"><strong class="ft-order-products-number">{{ $symbol }}{{ number_format($unitPrice, 2) }}</strong></td>
                            <td data-label="Line total"><strong class="ft-order-products-line-total">{{ $symbol }}{{ number_format($lineTotal, 2) }}</strong></td>
                            <td data-label="Notes / updated">
                                <div class="ft-order-products-notes">
                                    <span>{{ $item->notes ?: 'No notes' }}</span>
                                    <small>{{ $updatedBy }} · {{ $updatedAt }}</small>
                                </div>
                            </td>
                            <td data-label="Actions" class="ft-order-products-actions">
                                @if($item->id && $removed && $canEdit)
                                    <button type="button" class="btn small" wire:click="restoreJobItem({{ $item->id }})">Restore</button>
                                @elseif($item->id && $canEdit)
                                    <button
                                        type="button"
                                        class="ft-order-products-edit-btn"
                                        wire:click="openEditOrderProductModal({{ $item->id }})"
                                        aria-label="Edit {{ $item->product_name }}"
                                        title="Edit product"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z" />
                                        </svg>
                                        <span>Edit</span>
                                    </button>
                                @else
                                    <span class="ft-order-products-action-placeholder">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($showAddJobProductForm && $canCreate)
        <div class="ft-detail-products-inline-add" wire:key="order-add-product-inline-{{ $job->id }}" x-data x-on:keydown.escape.window="$wire.closeAddJobProductForm()">
            <x-catalog.detail-add-product
                :wire-key="'job-detail-add-product-'.$job->id"
                search-model="jobProductSearch"
                :search-value="$jobProductSearch"
                :search-results="$jobProductSearchResults"
                :result-total="$jobProductResultTotal"
                show-all-method="showAllJobProductResults"
                select-method="selectJobProduct"
                :selected-product="$jobProductSelectedProduct"
                :category-value="$jobProductCategory"
                quantity-model="jobProductQuantity"
                unit-price-model="jobProductUnitPrice"
                supplier-model="jobProductSupplierId"
                :supplier-value="$jobProductSupplierId"
                :supplier-label="$jobProductSupplierLabel"
                :supplier-locked="$jobProductSupplierLocked"
                :supplier-required="true"
                :currency-symbol="$symbol"
                close-method="closeAddJobProductForm"
                save-method="saveJobProduct({{ $job->id }})"
                selected-error-key="jobProductSelectedId"
                quantity-error-key="jobProductQuantity"
                unit-price-error-key="jobProductUnitPrice"
                supplier-error-key="jobProductSupplierId"
            />
        </div>
    @endif

    @if($canView && $canCreate && !$showAddJobProductForm && $items->isNotEmpty())
        <footer class="ft-order-products-ux-footer">
            <span>Product and quantity changes are recorded in order activity.</span>
            <button type="button" class="ft-outline-btn ft-order-product-add-another" wire:click="openAddJobProductForm({{ $job->id }})" wire:loading.attr="disabled" wire:target="openAddJobProductForm">
                ＋ Add another product
            </button>
        </footer>
    @endif

    @if($showEditOrderProductModal && $canEdit && $editOrderProductItemId)
        <div class="modal-wrap show" wire:key="prototype-edit-order-product-{{ $editOrderProductItemId }}" wire:click.self="closeEditOrderProductModal">
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="prototype-edit-product-title">
                <div class="modal-head">
                    <h2 id="prototype-edit-product-title">Edit Product &amp; Quantity</h2>
                    <button type="button" class="close" wire:click="closeEditOrderProductModal" aria-label="Close">✕</button>
                </div>
                <div class="modal-body">
                    <div class="field">
                        <label>Product</label>
                        <input value="{{ $editOrderProductName }}" readonly aria-readonly="true">
                    </div>
                    <div class="field">
                        <x-ui.search-select
                            label="Supplier *"
                            property="editOrderProductSupplierId"
                            type="suppliers"
                            context="order-detail-product-edit"
                            :value="$editOrderProductSupplierId"
                            :selected-label="$editOrderProductSupplierLabel ?: 'Select supplier'"
                            placeholder="Select supplier"
                            :clearable="false"
                            action="updateEditOrderProductSupplierFromSelector"
                            :menu-width="360"
                        />
                        @error('editOrderProductSupplierId')<div class="validation-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-grid">
                        <div class="field">
                            <label>Quantity</label>
                            <input type="number" min="1" wire:model="editOrderProductQuantity">
                            @error('editOrderProductQuantity')<div class="validation-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="field">
                            <label>Unit price</label>
                            <input type="number" min="0" step="0.01" wire:model="editOrderProductUnitPrice">
                            @error('editOrderProductUnitPrice')<div class="validation-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="field">
                        <label>Notes</label>
                        <input wire:model="editOrderProductNotes" maxlength="2000">
                        @error('editOrderProductNotes')<div class="validation-error">{{ $message }}</div>@enderror
                    </div>
                    @if($canDelete)
                        <div class="product-modal-remove">
                            <button type="button" class="btn danger small" wire:click="removeJobItem({{ $editOrderProductItemId }})" wire:confirm="Remove this product from the active order? It will remain visible for audit history." wire:loading.attr="disabled">Remove product</button>
                        </div>
                    @endif
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn" wire:click="closeEditOrderProductModal">Cancel</button>
                    <button type="button" class="btn primary" wire:click="saveEditOrderProductModal" wire:loading.attr="disabled" wire:target="saveEditOrderProductModal">
                        <span wire:loading.remove wire:target="saveEditOrderProductModal">Save Changes</span>
                        <span wire:loading wire:target="saveEditOrderProductModal">Saving…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</section>
