<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['invitation', 'token', 'quote', 'products', 'contact', 'rfqReference', 'locked' => false]));

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

foreach (array_filter((['invitation', 'token', 'quote', 'products', 'contact', 'rfqReference', 'locked' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $inquiry = $invitation->inquiry;
    $firstProduct = collect($products)->first();
?>
<form method="post" action="<?php echo e(route('rfq.public.respond', ['token' => $token])); ?>" id="rfq-details-form" class="ft-rfq-portal-stack">
    <?php echo csrf_field(); ?>
    <section class="ft-rfq-portal-card ft-rfq-portal-section-card">
        <div class="ft-rfq-portal-card__header">
            <div>
                <h2>Supplier and RFQ details</h2>
                <p>Confirm the contact information for this quotation request.</p>
            </div>
        </div>

        <div class="ft-rfq-details-grid">
            <div class="ft-rfq-readonly-field"><small>Supplier company</small><strong><?php echo e($invitation->supplier?->name ?: '—'); ?></strong></div>
            <div class="ft-rfq-readonly-field"><small>Inquiry reference</small><strong><?php echo e($inquiry->inquiry_number); ?></strong></div>
            <div class="ft-rfq-readonly-field"><small>RFQ reference</small><strong><?php echo e($rfqReference); ?></strong></div>
        </div>

        <div class="ft-rfq-form-grid is-three">
            <label>
                <span>Contact person *</span>
                <input type="text" name="supplier_contact_name" value="<?php echo e(old('supplier_contact_name', $contact['name'] ?? '')); ?>" required <?php if($locked): echo 'disabled'; endif; ?>>
            </label>
            <label>
                <span>Email *</span>
                <input type="email" name="supplier_contact_email" value="<?php echo e(old('supplier_contact_email', $contact['email'] ?? '')); ?>" required <?php if($locked): echo 'disabled'; endif; ?>>
            </label>
            <label>
                <span>Phone</span>
                <input type="text" name="supplier_contact_phone" value="<?php echo e(old('supplier_contact_phone', $contact['phone'] ?? '')); ?>" <?php if($locked): echo 'disabled'; endif; ?>>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="ft-rfq-product-line">
                    <?php if (isset($component)) { $__componentOriginal2b950ae2ac1d5af09e2e6f0db8d380c4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2b950ae2ac1d5af09e2e6f0db8d380c4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.product-thumb','data' => ['product' => $product,'size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.product-thumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product),'size' => 'lg']); ?>
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
                    <div class="ft-rfq-product-line__copy">
                        <strong><?php echo e($product['name']); ?></strong>
                        <span><?php echo e($product['code'] ?: 'Product'); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product['category']): ?> · <?php echo e($product['category']); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></span>
                    </div>
                    <div class="ft-rfq-product-line__quantity">
                        <small>Requested quantity</small>
                        <strong><?php echo e(number_format((float) $product['quantity'], fmod((float) $product['quantity'], 1.0) === 0.0 ? 0 : 2)); ?> <?php echo e($product['unit']); ?></strong>
                    </div>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </section>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($locked)): ?>
        <div class="ft-rfq-portal-bottom-actions">
            <span></span>
            <div>
                <button type="submit" class="ft-rfq-btn is-secondary" name="action" value="save_details"><?php if (isset($component)) { $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f = $component; } ?>
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
                <button type="submit" class="ft-rfq-btn is-primary" name="action" value="continue_pricing">Continue to pricing <?php if (isset($component)) { $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f = $component; } ?>
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
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/laravel/flowtrack/resources/views/components/rfq/public/details.blade.php ENDPATH**/ ?>