<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['invitation', 'token', 'quote', 'products', 'currency', 'locked' => false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['invitation', 'token', 'quote', 'products', 'currency', 'locked' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $quoteItems = collect($quote?->items ?? [])->keyBy('inquiry_item_id');
?>
<form method="post" action="<?php echo e(route('rfq.public.respond', ['token' => $token])); ?>" id="rfq-pricing-form" class="ft-rfq-portal-stack">
    <?php echo csrf_field(); ?>
    <section class="ft-rfq-portal-card ft-rfq-portal-section-card">
        <div class="ft-rfq-portal-card__header ft-rfq-card-header-with-select">
            <div>
                <h2>Product pricing</h2>
                <p>Enter unit pricing and minimum order quantity for each requested product.</p>
            </div>
            <label class="ft-rfq-inline-select">
                <span>Currency</span>
                <select name="currency" data-rfq-currency <?php if($locked): echo 'disabled'; endif; ?>>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP', 'CNY' => 'CNY']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($code); ?>" <?php if(old('currency', $currency) === $code): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </label>
        </div>

        <div class="ft-rfq-pricing-table-wrap">
            <table class="ft-rfq-pricing-table">
                <thead><tr><th>Product</th><th>Quantity</th><th>Unit price</th><th>MOQ</th><th>Subtotal</th></tr></thead>
                <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php ($item = $quoteItems->get($product['item_id'])); ?>
                    <tr data-rfq-price-row data-quantity="<?php echo e((float) $product['quantity']); ?>">
                        <td>
                            <div class="ft-rfq-table-product">
                                <?php if (isset($component)) { $__componentOriginal2b950ae2ac1d5af09e2e6f0db8d380c4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2b950ae2ac1d5af09e2e6f0db8d380c4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.product-thumb','data' => ['product' => $product,'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.product-thumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product),'size' => 'sm']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2b950ae2ac1d5af09e2e6f0db8d380c4)): ?>
<?php $attributes = $__attributesOriginal2b950ae2ac1d5af09e2e6f0db8d380c4; ?>
<?php unset($__attributesOriginal2b950ae2ac1d5af09e2e6f0db8d380c4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2b950ae2ac1d5af09e2e6f0db8d380c4)): ?>
<?php $component = $__componentOriginal2b950ae2ac1d5af09e2e6f0db8d380c4; ?>
<?php unset($__componentOriginal2b950ae2ac1d5af09e2e6f0db8d380c4); ?>
<?php endif; ?>
                                <span><strong><?php echo e($product['name']); ?></strong><small><?php echo e($product['code'] ?: 'Product'); ?></small></span>
                            </div>
                        </td>
                        <td><?php echo e(number_format((float) $product['quantity'], fmod((float) $product['quantity'], 1.0) === 0.0 ? 0 : 2)); ?></td>
                        <td><input type="number" name="prices[<?php echo e($product['item_id']); ?>]" value="<?php echo e(old('prices.'.$product['item_id'], $item?->unit_price)); ?>" min="0" step="0.0001" required data-rfq-price <?php if($locked): echo 'disabled'; endif; ?>></td>
                        <td><input type="number" name="moqs[<?php echo e($product['item_id']); ?>]" value="<?php echo e(old('moqs.'.$product['item_id'], $item?->moq)); ?>" min="0" step="1" <?php if($locked): echo 'disabled'; endif; ?>></td>
                        <td class="ft-rfq-subtotal-preview">Calculated on review</td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="ft-rfq-portal-card ft-rfq-portal-section-card">
        <div class="ft-rfq-portal-card__header"><div><h2>Pricing &amp; commercial terms</h2><p>Add setup, sample and other commercial costs.</p></div></div>
        <div class="ft-rfq-form-grid is-four">
            <label><span>Tooling / setup</span><input type="number" name="tooling_cost" value="<?php echo e(old('tooling_cost', $quote?->tooling_cost ?? 0)); ?>" min="0" step="0.01" <?php if($locked): echo 'disabled'; endif; ?>></label>
            <label><span>Sample cost</span><input type="number" name="sample_cost" value="<?php echo e(old('sample_cost', $quote?->sample_cost ?? 0)); ?>" min="0" step="0.01" <?php if($locked): echo 'disabled'; endif; ?>></label>
            <label><span>Freight / other costs</span><input type="number" name="freight" value="<?php echo e(old('freight', $quote?->freight ?? 0)); ?>" min="0" step="0.01" <?php if($locked): echo 'disabled'; endif; ?>></label>
            <label><span>Discount</span><input type="number" name="discount" value="<?php echo e(old('discount', $quote?->discount ?? 0)); ?>" min="0" step="0.01" <?php if($locked): echo 'disabled'; endif; ?>></label>
            <label><span>Tax</span><select name="tax_status" <?php if($locked): echo 'disabled'; endif; ?>><option value="excluded" <?php if(old('tax_status', $quote?->tax_status ?? 'excluded') === 'excluded'): echo 'selected'; endif; ?>>Excluded</option><option value="included" <?php if(old('tax_status', $quote?->tax_status) === 'included'): echo 'selected'; endif; ?>>Included</option></select></label>
            <label><span>Production lead time</span><div class="ft-rfq-input-suffix"><input type="number" name="lead_time_days" value="<?php echo e(old('lead_time_days', $quote?->lead_time_days)); ?>" min="0" <?php if($locked): echo 'disabled'; endif; ?>><span>days</span></div></label>
            <label><span>Sample lead time</span><div class="ft-rfq-input-suffix"><input type="number" name="sample_lead_time_days" value="<?php echo e(old('sample_lead_time_days', $quote?->sample_lead_time_days)); ?>" min="0" <?php if($locked): echo 'disabled'; endif; ?>><span>days</span></div></label>
            <label><span>Quote validity</span><div class="ft-rfq-input-suffix"><input type="number" name="validity_days" value="<?php echo e(old('validity_days', $quote?->validity_days ?? 30)); ?>" min="0" <?php if($locked): echo 'disabled'; endif; ?>><span>days</span></div></label>
            <label><span>Incoterm</span><input type="text" name="incoterm" value="<?php echo e(old('incoterm', $quote?->incoterm)); ?>" placeholder="FOB" <?php if($locked): echo 'disabled'; endif; ?>></label>
            <label><span>Shipping port</span><input type="text" name="shipping_port" value="<?php echo e(old('shipping_port', $quote?->shipping_port)); ?>" placeholder="Shanghai" <?php if($locked): echo 'disabled'; endif; ?>></label>
            <label><span>Estimated delivery</span><input type="date" name="estimated_delivery_date" value="<?php echo e(old('estimated_delivery_date', $quote?->estimated_delivery_date?->format('Y-m-d'))); ?>" <?php if($locked): echo 'disabled'; endif; ?>></label>
            <label><span>Specification compliance</span><select name="specification_compliance" <?php if($locked): echo 'disabled'; endif; ?>><option value="">Select</option><option value="yes" <?php if(old('specification_compliance', $quote?->specification_compliance) === 'yes'): echo 'selected'; endif; ?>>Yes, fully compliant</option><option value="partial" <?php if(old('specification_compliance', $quote?->specification_compliance) === 'partial'): echo 'selected'; endif; ?>>Partially compliant</option><option value="no" <?php if(old('specification_compliance', $quote?->specification_compliance) === 'no'): echo 'selected'; endif; ?>>Not compliant</option></select></label>
            <label class="is-full"><span>Pricing / production notes</span><textarea name="notes" rows="3" <?php if($locked): echo 'disabled'; endif; ?> placeholder="Add packaging, sample availability or production notes."><?php echo e(old('notes', $quote?->notes)); ?></textarea></label>
        </div>
        <div class="ft-rfq-live-total-row"><span>Estimated quoted value</span><strong data-rfq-live-total><?php echo e($currency); ?> <?php echo e(number_format((float) ($quote?->submitted_total ?? 0), 2)); ?></strong></div>
    </section>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($locked)): ?>
        <div class="ft-rfq-portal-bottom-actions">
            <a class="ft-rfq-btn is-secondary" href="<?php echo e(route('rfq.public.show', ['token' => $token, 'step' => 'details'])); ?>"><?php if (isset($component)) { $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.icon','data' => ['name' => 'arrow-left']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-left']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f)): ?>
<?php $attributes = $__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f; ?>
<?php unset($__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal19d5a25445acdc2666ec5d32271cdd6f)): ?>
<?php $component = $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f; ?>
<?php unset($__componentOriginal19d5a25445acdc2666ec5d32271cdd6f); ?>
<?php endif; ?> Back to product details</a>
            <div>
                <button type="submit" class="ft-rfq-btn is-secondary" name="action" value="save_pricing"><?php if (isset($component)) { $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.icon','data' => ['name' => 'save']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'save']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f)): ?>
<?php $attributes = $__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f; ?>
<?php unset($__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal19d5a25445acdc2666ec5d32271cdd6f)): ?>
<?php $component = $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f; ?>
<?php unset($__componentOriginal19d5a25445acdc2666ec5d32271cdd6f); ?>
<?php endif; ?> Save draft</button>
                <button type="submit" class="ft-rfq-btn is-primary" name="action" value="continue_documents">Continue to documents <?php if (isset($component)) { $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.icon','data' => ['name' => 'chevron-right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-right']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f)): ?>
<?php $attributes = $__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f; ?>
<?php unset($__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal19d5a25445acdc2666ec5d32271cdd6f)): ?>
<?php $component = $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f; ?>
<?php unset($__componentOriginal19d5a25445acdc2666ec5d32271cdd6f); ?>
<?php endif; ?></button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</form>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/laravel/flowtrack/resources/views/components/rfq/public/pricing.blade.php ENDPATH**/ ?>