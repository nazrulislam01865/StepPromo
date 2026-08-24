    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="ft-order-list-flash" role="status"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <header class="list-head">
        <div>
            <div class="breadcrumbs">Order / Orders</div>
            <h1>Orders</h1>
            <p class="sub">Manage active orders, see the exact workflow stage, and open the next required action.</p>
        </div>
        <div class="top-actions">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('jobs.create')): ?>
                <a class="btn" href="<?php echo e(route('orders.bulk-import')); ?>">⇧ Bulk order</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canModule('jobs', 'create')): ?>
                <a class="btn primary" href="<?php echo e(route('jobs.index', ['create' => 1])); ?>" wire:navigate>＋ Create order</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </header>

    <section class="list-card stage-overview-card" aria-label="Orders by workflow stage">
        <div class="list-section-head">
            <div>
                <h2>Orders by workflow stage</h2>
                <p>Click a stage to filter the orders below on this page.</p>
            </div>
            <button class="btn small primary" type="button" wire:click="selectStage(null)">Show all</button>
        </div>
        <div class="list-stage-strip">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $isSelectedStage = (string) $phaseFilter === (string) data_get($stage, 'id');
                ?>
                <button
                    type="button"
                    class="list-stage-card <?php echo e($isSelectedStage ? 'active' : ''); ?>"
                    style="--stage:<?php echo e(data_get($stage, 'color', '#2d72d9')); ?>;--stage-text:<?php echo e($stageTextColor(data_get($stage, 'color', '#2d72d9'))); ?>"
                    wire:click="selectStage(<?php echo e((int) data_get($stage, 'id')); ?>)"
                    aria-pressed="<?php echo e($isSelectedStage ? 'true' : 'false'); ?>"
                >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSelectedStage): ?>
                        <span class="list-stage-selected-badge" aria-hidden="true"><i>✓</i> Selected</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="list-stage-kicker">Stage <?php echo e((int) data_get($stage, 'sequence')); ?></span>
                    <b title="<?php echo e(data_get($stage, 'name')); ?>"><?php echo e(data_get($stage, 'short_name') ?: data_get($stage, 'name')); ?></b>
                    <span class="list-stage-count"><em>Current orders</em><strong><?php echo e(number_format((int) data_get($stage, 'count', 0))); ?></strong></span>
                </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </section>

<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/laravel/flowtrack/resources/views/components/orders/list/header-and-stages.blade.php ENDPATH**/ ?>