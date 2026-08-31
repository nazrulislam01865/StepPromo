<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="color-scheme" content="light">
    <title><?php echo e($invitation->inquiry->inquiry_number); ?> · Supplier quotation</title>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($brand['favicon_url'] ?? null): ?><link rel="icon" href="<?php echo e($brand['favicon_url']); ?>"><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <script
        src="<?php echo e(asset('js/flowtrack-image-fallback.js')); ?>?v=<?php echo e(\App\Support\FrontendBuildVersion::current()); ?>"
        data-fallback-src="<?php echo e(asset('images/flowtrack-image-fallback.svg')); ?>"
    ></script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/theme/flowtrack/core.css', 'resources/css/app.css']); ?>
</head>
<body class="ft-rfq-portal-page">
    <?php if (isset($component)) { $__componentOriginal97c2010a92ab8b8641dfc57fd6f868eb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal97c2010a92ab8b8641dfc57fd6f868eb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.header','data' => ['brand' => $brand,'supplier' => $invitation->supplier,'buyerEmail' => $buyerEmail]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['brand' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($brand),'supplier' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invitation->supplier),'buyer-email' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($buyerEmail)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal97c2010a92ab8b8641dfc57fd6f868eb)): ?>
<?php $attributes = $__attributesOriginal97c2010a92ab8b8641dfc57fd6f868eb; ?>
<?php unset($__attributesOriginal97c2010a92ab8b8641dfc57fd6f868eb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal97c2010a92ab8b8641dfc57fd6f868eb)): ?>
<?php $component = $__componentOriginal97c2010a92ab8b8641dfc57fd6f868eb; ?>
<?php unset($__componentOriginal97c2010a92ab8b8641dfc57fd6f868eb); ?>
<?php endif; ?>

    <main class="ft-rfq-portal-shell">
        <section class="ft-rfq-portal-main">
            <div class="ft-rfq-portal-title-row">
                <div>
                    <a class="ft-rfq-portal-backlink" href="<?php echo e(route('rfq.public.show', ['token' => $token, 'step' => 'details'])); ?>">
                        <?php if (isset($component)) { $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f = $component; } ?>
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
<?php endif; ?> Quotation request
                    </a>
                    <h1>Submit your quotation</h1>
                    <p>Provide your best commercial offer for the product below.</p>
                </div>
                <div class="ft-rfq-portal-save-state" aria-live="polite">
                    <span class="ft-rfq-portal-draft-pill"><?php echo e($submitted ? 'Submitted' : 'Draft'); ?></span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($savedAt): ?>
                        <small><?php if (isset($component)) { $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.icon','data' => ['name' => 'clock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clock']); ?>
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
<?php endif; ?> Saved <?php echo e($savedAt->diffForHumans()); ?></small>
                    <?php else: ?>
                        <small><?php if (isset($component)) { $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.icon','data' => ['name' => 'clock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clock']); ?>
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
<?php endif; ?> Not saved yet</small>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="ft-rfq-portal-private-note">
                <span class="ft-rfq-portal-lock-icon"><?php if (isset($component)) { $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.icon','data' => ['name' => 'lock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'lock']); ?>
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
<?php endif; ?></span>
                <span>This quotation is private and can only be accessed through your invitation link.</span>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                <div class="ft-rfq-portal-feedback is-success" role="status">
                    <?php if (isset($component)) { $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.icon','data' => ['name' => 'check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check']); ?>
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
<?php endif; ?><?php echo e(session('success')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="ft-rfq-portal-feedback is-error" role="alert">
                    <?php if (isset($component)) { $__componentOriginal19d5a25445acdc2666ec5d32271cdd6f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal19d5a25445acdc2666ec5d32271cdd6f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.icon','data' => ['name' => 'alert']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'alert']); ?>
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
<?php endif; ?><?php echo e($errors->first()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal056d4d9818d870afc81b7c8c88fea167 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal056d4d9818d870afc81b7c8c88fea167 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.stepper','data' => ['steps' => $steps,'token' => $token,'locked' => $locked]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.stepper'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['steps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($steps),'token' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($token),'locked' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($locked)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal056d4d9818d870afc81b7c8c88fea167)): ?>
<?php $attributes = $__attributesOriginal056d4d9818d870afc81b7c8c88fea167; ?>
<?php unset($__attributesOriginal056d4d9818d870afc81b7c8c88fea167); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal056d4d9818d870afc81b7c8c88fea167)): ?>
<?php $component = $__componentOriginal056d4d9818d870afc81b7c8c88fea167; ?>
<?php unset($__componentOriginal056d4d9818d870afc81b7c8c88fea167); ?>
<?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($step):
                case ('pricing'): ?>
                    <?php if (isset($component)) { $__componentOriginal2857955b09444b79b38ac83657192022 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2857955b09444b79b38ac83657192022 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.pricing','data' => ['invitation' => $invitation,'token' => $token,'quote' => $quote,'products' => $products,'currency' => $currency,'locked' => $locked]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.pricing'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['invitation' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invitation),'token' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($token),'quote' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quote),'products' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($products),'currency' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currency),'locked' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($locked)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2857955b09444b79b38ac83657192022)): ?>
<?php $attributes = $__attributesOriginal2857955b09444b79b38ac83657192022; ?>
<?php unset($__attributesOriginal2857955b09444b79b38ac83657192022); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2857955b09444b79b38ac83657192022)): ?>
<?php $component = $__componentOriginal2857955b09444b79b38ac83657192022; ?>
<?php unset($__componentOriginal2857955b09444b79b38ac83657192022); ?>
<?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>
                <?php case ('documents'): ?>
                    <?php if (isset($component)) { $__componentOriginal6cdcd6058dd4e7b8de31da5daade762b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6cdcd6058dd4e7b8de31da5daade762b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.documents','data' => ['invitation' => $invitation,'token' => $token,'quote' => $quote,'products' => $products,'documents' => $documents,'documentTypes' => $documentTypes,'requiredDocumentTypes' => $requiredDocumentTypes,'requiredDocumentCount' => $requiredDocumentCount,'requiredDocumentTotal' => $requiredDocumentTotal,'supportingInformationOptions' => $supportingInformationOptions,'supportingInformation' => $supportingInformation,'locked' => $locked]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.documents'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['invitation' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invitation),'token' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($token),'quote' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quote),'products' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($products),'documents' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documents),'document-types' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documentTypes),'required-document-types' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($requiredDocumentTypes),'required-document-count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($requiredDocumentCount),'required-document-total' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($requiredDocumentTotal),'supporting-information-options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supportingInformationOptions),'supporting-information' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supportingInformation),'locked' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($locked)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6cdcd6058dd4e7b8de31da5daade762b)): ?>
<?php $attributes = $__attributesOriginal6cdcd6058dd4e7b8de31da5daade762b; ?>
<?php unset($__attributesOriginal6cdcd6058dd4e7b8de31da5daade762b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6cdcd6058dd4e7b8de31da5daade762b)): ?>
<?php $component = $__componentOriginal6cdcd6058dd4e7b8de31da5daade762b; ?>
<?php unset($__componentOriginal6cdcd6058dd4e7b8de31da5daade762b); ?>
<?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>
                <?php case ('review'): ?>
                    <?php if (isset($component)) { $__componentOriginalef461f20fd06b11acc6ee7e3699c312c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalef461f20fd06b11acc6ee7e3699c312c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.review','data' => ['invitation' => $invitation,'token' => $token,'quote' => $quote,'products' => $products,'documents' => $documents,'documentTypes' => $documentTypes,'contact' => $contact,'rfqReference' => $rfqReference,'currency' => $currency,'productSubtotal' => $productSubtotal,'sampleCost' => $sampleCost,'otherCosts' => $otherCosts,'totalQuotedValue' => $totalQuotedValue,'clientName' => $clientName,'readyToSubmit' => $readyToSubmit,'locked' => $locked,'submitted' => $submitted]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.review'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['invitation' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invitation),'token' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($token),'quote' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quote),'products' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($products),'documents' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documents),'document-types' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documentTypes),'contact' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contact),'rfq-reference' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rfqReference),'currency' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currency),'product-subtotal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($productSubtotal),'sample-cost' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sampleCost),'other-costs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($otherCosts),'total-quoted-value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalQuotedValue),'client-name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($clientName),'ready-to-submit' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($readyToSubmit),'locked' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($locked),'submitted' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($submitted)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalef461f20fd06b11acc6ee7e3699c312c)): ?>
<?php $attributes = $__attributesOriginalef461f20fd06b11acc6ee7e3699c312c; ?>
<?php unset($__attributesOriginalef461f20fd06b11acc6ee7e3699c312c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalef461f20fd06b11acc6ee7e3699c312c)): ?>
<?php $component = $__componentOriginalef461f20fd06b11acc6ee7e3699c312c; ?>
<?php unset($__componentOriginalef461f20fd06b11acc6ee7e3699c312c); ?>
<?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php break; ?>
                <?php default: ?>
                    <?php if (isset($component)) { $__componentOriginala4b8e8f871ebd061a7794650b7016381 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala4b8e8f871ebd061a7794650b7016381 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.details','data' => ['invitation' => $invitation,'token' => $token,'quote' => $quote,'products' => $products,'contact' => $contact,'rfqReference' => $rfqReference,'locked' => $locked]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.details'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['invitation' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invitation),'token' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($token),'quote' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quote),'products' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($products),'contact' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($contact),'rfq-reference' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rfqReference),'locked' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($locked)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala4b8e8f871ebd061a7794650b7016381)): ?>
<?php $attributes = $__attributesOriginala4b8e8f871ebd061a7794650b7016381; ?>
<?php unset($__attributesOriginala4b8e8f871ebd061a7794650b7016381); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala4b8e8f871ebd061a7794650b7016381)): ?>
<?php $component = $__componentOriginala4b8e8f871ebd061a7794650b7016381; ?>
<?php unset($__componentOriginala4b8e8f871ebd061a7794650b7016381); ?>
<?php endif; ?>
            <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        <aside class="ft-rfq-portal-aside">
            <?php if (isset($component)) { $__componentOriginal37e66c74732ad230bfbb002c81f79b2d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal37e66c74732ad230bfbb002c81f79b2d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.summary','data' => ['invitation' => $invitation,'token' => $token,'step' => $step,'quote' => $quote,'firstProduct' => $firstProduct,'currency' => $currency,'totalQuantity' => $totalQuantity,'productSubtotal' => $productSubtotal,'sampleCost' => $sampleCost,'otherCosts' => $otherCosts,'totalQuotedValue' => $totalQuotedValue,'detailsComplete' => $detailsComplete,'pricingComplete' => $pricingComplete,'documents' => $documents,'documentsComplete' => $documentsComplete,'readyToSubmit' => $readyToSubmit,'locked' => $locked,'submitted' => $submitted]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.summary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['invitation' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invitation),'token' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($token),'step' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($step),'quote' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quote),'first-product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstProduct),'currency' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currency),'total-quantity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalQuantity),'product-subtotal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($productSubtotal),'sample-cost' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sampleCost),'other-costs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($otherCosts),'total-quoted-value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalQuotedValue),'details-complete' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($detailsComplete),'pricing-complete' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pricingComplete),'documents' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documents),'documents-complete' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documentsComplete),'ready-to-submit' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($readyToSubmit),'locked' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($locked),'submitted' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($submitted)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal37e66c74732ad230bfbb002c81f79b2d)): ?>
<?php $attributes = $__attributesOriginal37e66c74732ad230bfbb002c81f79b2d; ?>
<?php unset($__attributesOriginal37e66c74732ad230bfbb002c81f79b2d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal37e66c74732ad230bfbb002c81f79b2d)): ?>
<?php $component = $__componentOriginal37e66c74732ad230bfbb002c81f79b2d; ?>
<?php unset($__componentOriginal37e66c74732ad230bfbb002c81f79b2d); ?>
<?php endif; ?>
        </aside>
    </main>

    <?php if (isset($component)) { $__componentOriginal2763c2689a17455a36e00bff18a73933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2763c2689a17455a36e00bff18a73933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rfq.public.footer','data' => ['brand' => $brand,'buyerEmail' => $buyerEmail]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rfq.public.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['brand' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($brand),'buyer-email' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($buyerEmail)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2763c2689a17455a36e00bff18a73933)): ?>
<?php $attributes = $__attributesOriginal2763c2689a17455a36e00bff18a73933; ?>
<?php unset($__attributesOriginal2763c2689a17455a36e00bff18a73933); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2763c2689a17455a36e00bff18a73933)): ?>
<?php $component = $__componentOriginal2763c2689a17455a36e00bff18a73933; ?>
<?php unset($__componentOriginal2763c2689a17455a36e00bff18a73933); ?>
<?php endif; ?>

    <script>
    (() => {
        const upload = document.querySelector('[data-rfq-document-input]');
        if (upload) {
            upload.addEventListener('change', () => {
                if (upload.files && upload.files.length && upload.form) {
                    upload.form.action = upload.dataset.uploadUrl || upload.form.action;
                    upload.form.submit();
                }
            });
        }
        const dropZone = document.querySelector('[data-rfq-dropzone]');
        if (dropZone && upload) {
            ['dragenter', 'dragover'].forEach(name => dropZone.addEventListener(name, event => {
                event.preventDefault();
                dropZone.classList.add('is-dragging');
            }));
            ['dragleave', 'drop'].forEach(name => dropZone.addEventListener(name, event => {
                event.preventDefault();
                dropZone.classList.remove('is-dragging');
            }));
            dropZone.addEventListener('drop', event => {
                if (!event.dataTransfer?.files?.length) return;
                const transfer = new DataTransfer();
                Array.from(event.dataTransfer.files).forEach(file => transfer.items.add(file));
                upload.files = transfer.files;
                if (upload.form) {
                    upload.form.action = upload.dataset.uploadUrl || upload.form.action;
                    upload.form.submit();
                }
            });
        }

        const pricing = document.getElementById('rfq-pricing-form');
        if (pricing) {
            const currency = pricing.querySelector('[data-rfq-currency]');
            const totalNodes = document.querySelectorAll('[data-rfq-live-total]');
            const recalculate = () => {
                let subtotal = 0;
                pricing.querySelectorAll('[data-rfq-price]').forEach(input => {
                    const row = input.closest('[data-rfq-price-row]');
                    subtotal += (Number(row?.dataset.quantity || 0) * Number(input.value || 0));
                });
                const money = name => Number(pricing.querySelector(`[name="${name}"]`)?.value || 0);
                const total = subtotal + money('tooling_cost') + money('sample_cost') + money('freight') - money('discount');
                const code = currency?.value || 'USD';
                totalNodes.forEach(node => node.textContent = `${code} ${total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
            };
            pricing.addEventListener('input', recalculate);
            pricing.addEventListener('change', recalculate);
            recalculate();
        }

        document.querySelectorAll('[data-rfq-decline]').forEach(button => button.addEventListener('click', event => {
            if (!window.confirm('Decline this request for quotation?')) event.preventDefault();
        }));
    })();
    </script>
</body>
</html>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/laravel/flowtrack/resources/views/rfq/public-show.blade.php ENDPATH**/ ?>