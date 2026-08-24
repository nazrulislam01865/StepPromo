<?php

namespace App\Livewire\Jobs\Concerns;

use App\Actions\Orders\AddOrderItem;
use App\Actions\Orders\RemoveOrderItem;
use App\Actions\Orders\RestoreOrderItem;
use App\Actions\Orders\UpdateOrderItemDetails;
use App\Actions\Orders\UpdateOrderItemField;
use App\Queries\Orders\VisibleOrderQuery;
use App\Models\FlowJobItem;
use App\Models\MasterRecord;
use App\Services\AccessControlService;
use App\Services\MasterDataService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Renderless;

/**
 * Phase 5 Order UI workflow extracted from the legacy Jobs coordinator.
 *
 * Public method names and parent Livewire state are intentionally preserved so
 * existing Blade bindings, deep links, validation keys and realtime behavior do
 * not change during the incremental decomposition.
 */
trait ManagesOrderProducts
{
    /**
     * Persist a supplier selected by the reusable remote search control.
     *
     * The UI passes a namespaced property token rather than binding a temporary
     * Livewire field per row. This keeps product rows lightweight while still
     * routing the update through the same authorization/audit service path.
     */
    public function openEditOrderProductModal(int $itemId): void
    {
        abort_unless($this->selectedJobId && $this->detailTab === 'overview', 422);

        $user = auth()->user();
        $job = app(VisibleOrderQuery::class)->base($user, $this->selectedJobId);
        abort_unless(app(AccessControlService::class)->can($user, 'catalog_products', 'edit'), 403);

        $item = FlowJobItem::query()
            ->where('flow_job_id', $job->id)
            ->with(['supplier:id,name,code,type,status', 'catalogProduct:id,name,code'])
            ->findOrFail($itemId);
        abort_if((bool) ($item->is_removed ?? false), 422, 'Restore this product before editing it.');

        $this->editOrderProductItemId = (int) $item->id;
        $this->editOrderProductName = (string) ($item->product_name ?: $item->catalogProduct?->name ?: '');
        $this->editOrderProductCode = (string) ($item->catalogProduct?->code ?: '');
        $this->editOrderProductSupplierId = $item->supplier_id ? (int) $item->supplier_id : null;
        $this->editOrderProductSupplierLabel = (string) ($item->supplier?->name ?: 'Select supplier');
        $this->editOrderProductQuantity = (string) max(1, (int) ($item->quantity ?? 1));
        $this->editOrderProductUnitPrice = number_format((float) ($item->unit_price ?? 0), 2, '.', '');
        $this->editOrderProductNotes = (string) ($item->notes ?? '');
        $this->showEditOrderProductModal = true;
        $this->resetValidation([
            'editOrderProductSupplierId',
            'editOrderProductQuantity',
            'editOrderProductUnitPrice',
            'editOrderProductNotes',
        ]);
    }

    public function closeEditOrderProductModal(): void
    {
        $this->showEditOrderProductModal = false;
        $this->editOrderProductItemId = null;
        $this->editOrderProductName = '';
        $this->editOrderProductCode = '';
        $this->editOrderProductSupplierId = null;
        $this->editOrderProductSupplierLabel = '';
        $this->editOrderProductQuantity = '1';
        $this->editOrderProductUnitPrice = '0.00';
        $this->editOrderProductNotes = '';
        $this->resetValidation([
            'editOrderProductSupplierId',
            'editOrderProductQuantity',
            'editOrderProductUnitPrice',
            'editOrderProductNotes',
        ]);
    }

    #[Renderless]
    public function updateEditOrderProductSupplierFromSelector(string $property, mixed $supplierId): array
    {
        abort_unless($property === 'editOrderProductSupplierId', 422, 'Invalid supplier target.');
        $supplierId = filled($supplierId) ? (int) $supplierId : null;
        abort_unless($supplierId, 422, 'Select a supplier for this product.');

        $supplier = MasterRecord::query()
            ->forWorkspace(app(MasterDataService::class)->workspaceId())
            ->ofType('supplier')
            ->active()
            ->findOrFail($supplierId);

        $this->editOrderProductSupplierId = (int) $supplier->id;
        $this->editOrderProductSupplierLabel = (string) $supplier->name;

        return ['ok' => true, 'value' => (string) $supplier->id, 'label' => (string) $supplier->name];
    }

    public function saveEditOrderProductModal(): void
    {
        abort_unless($this->selectedJobId && $this->editOrderProductItemId, 422);

        $data = $this->validate([
            'editOrderProductSupplierId' => ['required', 'integer', 'min:1'],
            'editOrderProductQuantity' => ['required', 'integer', 'min:1'],
            'editOrderProductUnitPrice' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'editOrderProductNotes' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = auth()->user();
        $job = app(VisibleOrderQuery::class)->base($user, $this->selectedJobId);
        $item = FlowJobItem::query()->where('flow_job_id', $job->id)->findOrFail($this->editOrderProductItemId);

        app(UpdateOrderItemDetails::class)->handle($job, $item, [
            'supplier_id' => (int) $data['editOrderProductSupplierId'],
            'quantity' => (int) $data['editOrderProductQuantity'],
            'unit_price' => (float) $data['editOrderProductUnitPrice'],
            'notes' => trim((string) ($data['editOrderProductNotes'] ?? '')),
        ], $user);

        $this->closeEditOrderProductModal();
        session()->flash('success', 'Order product updated.');
    }

    #[Renderless]
    public function updateJobItemSupplierFromSelector(string $property, mixed $supplierId): array
    {
        abort_unless(preg_match('/^job-item-supplier:(\d+)$/', $property, $matches) === 1, 422, 'Invalid product supplier target.');

        return $this->updateJobItem((int) $matches[1], 'supplier_id', $supplierId);
    }

    #[Renderless]
    public function updateJobItem(int $itemId, string $field, mixed $value): array
    {
        $label = match ($field) {
            'category_name' => 'product category',
            'product_name' => 'product',
            'quantity' => 'quantity',
            'unit_price' => 'unit price',
            'notes' => 'product notes',
            'supplier_id' => 'supplier',
            default => 'product detail',
        };

        return $this->persistInlineEdit($label, function () use ($itemId, $field, $value) {
            $user = auth()->user();
            abort_unless($this->selectedJobId, 422);

            $job = app(VisibleOrderQuery::class)->detail($user, $this->selectedJobId);
            $item = FlowJobItem::where('flow_job_id', $job->id)->findOrFail($itemId);

            if ($field === 'category_name') {
                abort_unless(app(MasterDataService::class)->active('product_category')->contains('name', (string) $value), 422, 'Select a valid active product category.');
            }
            if ($field === 'product_name') {
                abort_if(blank($item->category_name), 422, 'Select a product category first.');
                $validProduct = app(\App\Services\FilterOptionService::class)
                    ->options($user, 'products', 'job-detail', '', (string) $value, 20, [
                        'category' => (string) $item->category_name,
                    ])
                    ->contains(fn ($option) => (string) ($option['id'] ?? '') === (string) $value);
                abort_unless($validProduct, 422, 'Select a valid active product for this category.');
            }

            app(UpdateOrderItemField::class)->handle($job, $item, $field, $value, $user);
        });
    }

    public function openAddJobProductForm(int $jobId): void
    {
        $user = auth()->user();
        $job = app(VisibleOrderQuery::class)->detail($user, $jobId);
        abort_unless(
            app(AccessControlService::class)->canEditVisibleJob($user, $job)
            && app(AccessControlService::class)->can($user, 'catalog_products', 'view')
            && app(AccessControlService::class)->can($user, 'catalog_products', 'create'),
            403
        );

        $this->resetValidation([
            'jobProductSelectedId', 'jobProductCategory', 'jobProductQuantity', 'jobProductUnitPrice', 'jobProductSupplierId',
        ]);
        $this->jobProductSearch = '';
        $this->jobProductShowAllResults = false;
        $this->jobProductSelectedId = null;
        $this->jobProductCategory = '';
        $this->jobProductQuantity = '1';
        $this->jobProductUnitPrice = '0.00';
        $this->jobProductSupplierId = null;
        $this->jobProductSupplierLabel = '';
        $this->jobProductSupplierLocked = false;
        $this->showAddJobProductForm = true;
    }

    public function closeAddJobProductForm(): void
    {
        $this->showAddJobProductForm = false;
        $this->jobProductSearch = '';
        $this->jobProductShowAllResults = false;
        $this->jobProductSelectedId = null;
        $this->jobProductCategory = '';
        $this->jobProductQuantity = '1';
        $this->jobProductUnitPrice = '0.00';
        $this->jobProductSupplierId = null;
        $this->jobProductSupplierLabel = '';
        $this->jobProductSupplierLocked = false;
        $this->resetValidation([
            'jobProductSelectedId', 'jobProductCategory', 'jobProductQuantity', 'jobProductUnitPrice', 'jobProductSupplierId',
        ]);
    }

    public function showAllJobProductResults(): void
    {
        abort_unless($this->showAddJobProductForm, 422);
        $this->jobProductShowAllResults = true;
    }

    public function selectJobProduct(int $productId): void
    {
        abort_unless($this->showAddJobProductForm && $this->selectedJobId, 422);
        $user = auth()->user();
        $job = app(VisibleOrderQuery::class)->detail($user, $this->selectedJobId);
        abort_unless(
            app(AccessControlService::class)->canEditVisibleJob($user, $job)
            && app(AccessControlService::class)->can($user, 'catalog_products', 'view')
            && app(AccessControlService::class)->can($user, 'catalog_products', 'create'),
            403
        );

        $product = app(\App\Services\ProductCatalogService::class)->findActiveProductOrFail($productId);
        $category = trim((string) ($product->parent?->name ?? ''));
        if ($category === '') {
            $legacy = trim((string) $product->description);
            $category = trim(explode(' ·', $legacy, 2)[0]);
        }

        $linkedSupplier = app(\App\Services\ProductCatalogService::class)->supplierForProduct($product);

        $this->jobProductSelectedId = (int) $product->id;
        $this->jobProductCategory = $category !== '' ? $category : 'Uncategorized';
        $this->jobProductSearch = (string) $product->name;
        $this->jobProductSupplierId = $linkedSupplier ? (int) $linkedSupplier->id : null;
        $this->jobProductSupplierLabel = $linkedSupplier ? (string) $linkedSupplier->name : '';
        $this->jobProductSupplierLocked = (bool) $linkedSupplier;
        $this->resetValidation(['jobProductSelectedId', 'jobProductCategory', 'jobProductSupplierId']);
    }

    #[Renderless]
    public function updateAddJobProductSupplierFromSelector(string $property, mixed $supplierId): array
    {
        abort_unless($property === 'jobProductSupplierId', 422, 'Invalid supplier target.');
        abort_unless($this->showAddJobProductForm && $this->selectedJobId && $this->jobProductSelectedId, 422);

        $user = auth()->user();
        $job = app(VisibleOrderQuery::class)->detail($user, $this->selectedJobId);
        abort_unless(
            app(AccessControlService::class)->canEditVisibleJob($user, $job)
            && app(AccessControlService::class)->can($user, 'catalog_products', 'view')
            && app(AccessControlService::class)->can($user, 'catalog_products', 'create'),
            403
        );

        $product = app(\App\Services\ProductCatalogService::class)
            ->findActiveProductOrFail((int) $this->jobProductSelectedId);
        $linkedSupplier = app(\App\Services\ProductCatalogService::class)->supplierForProduct($product);

        // Product Master remains authoritative. A manual supplier is available
        // only when the selected Product has no supplier linked in Product Master.
        if ($linkedSupplier) {
            $this->jobProductSupplierId = (int) $linkedSupplier->id;
            $this->jobProductSupplierLabel = (string) $linkedSupplier->name;
            $this->jobProductSupplierLocked = true;
            $this->resetValidation('jobProductSupplierId');

            return ['ok' => true, 'value' => (string) $linkedSupplier->id, 'label' => (string) $linkedSupplier->name];
        }

        $supplierId = filled($supplierId) ? (int) $supplierId : null;
        abort_unless($supplierId, 422, 'Select a supplier for this product.');

        $supplier = MasterRecord::query()
            ->forWorkspace(app(MasterDataService::class)->workspaceId())
            ->ofType('supplier')
            ->active()
            ->findOrFail($supplierId);

        $this->jobProductSupplierId = (int) $supplier->id;
        $this->jobProductSupplierLabel = (string) $supplier->name;
        $this->jobProductSupplierLocked = false;
        $this->resetValidation('jobProductSupplierId');

        return ['ok' => true, 'value' => (string) $supplier->id, 'label' => (string) $supplier->name];
    }

    public function saveJobProduct(int $jobId): void
    {
        abort_unless($this->showAddJobProductForm, 422);
        $user = auth()->user();
        $job = app(VisibleOrderQuery::class)->detail($user, $jobId);
        abort_unless((int) $this->selectedJobId === (int) $job->id, 422);
        abort_unless(
            app(AccessControlService::class)->canEditVisibleJob($user, $job)
            && app(AccessControlService::class)->can($user, 'catalog_products', 'view')
            && app(AccessControlService::class)->can($user, 'catalog_products', 'create'),
            403
        );

        $data = $this->validate([
            'jobProductSelectedId' => ['required', 'integer'],
            'jobProductSupplierId' => [
                'required',
                'integer',
                Rule::exists('master_records', 'id')->where(fn ($query) => $query
                    ->where('workspace_id', app(MasterDataService::class)->workspaceId())
                    ->where('type', 'supplier')
                    ->where('status', 'active')
                    ->whereNull('deleted_at')),
            ],
            'jobProductQuantity' => ['required', 'integer', 'min:1', 'max:999999999'],
            'jobProductUnitPrice' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
        ], [
            'jobProductSelectedId.required' => 'Select a product first.',
            'jobProductSupplierId.required' => 'Select a supplier for this product.',
            'jobProductQuantity.required' => 'Enter a quantity.',
            'jobProductUnitPrice.required' => 'Enter a unit price.',
        ]);

        $product = app(\App\Services\ProductCatalogService::class)
            ->findActiveProductOrFail((int) $data['jobProductSelectedId']);
        $category = trim((string) ($product->parent?->name ?? ''));
        if ($category === '') {
            $legacy = trim((string) $product->description);
            $category = trim(explode(' ·', $legacy, 2)[0]);
        }
        $category = $category !== '' ? $category : 'Uncategorized';

        $alreadyAdded = $job->items()
            ->whereRaw('LOWER(product_name) = ?', [mb_strtolower((string) $product->name)])
            ->exists();
        if ($alreadyAdded) {
            $this->addError('jobProductSelectedId', 'This product is already added to the Order.');
            return;
        }

        $linkedSupplier = app(\App\Services\ProductCatalogService::class)->supplierForProduct($product);
        $supplierId = $linkedSupplier
            ? (int) $linkedSupplier->id
            : (int) $data['jobProductSupplierId'];

        app(AddOrderItem::class)->handle(
            $job,
            $category,
            (string) $product->name,
            (int) $data['jobProductQuantity'],
            $user,
            (float) $data['jobProductUnitPrice'],
            (int) $data['jobProductSelectedId'],
            $supplierId,
        );

        $this->closeAddJobProductForm();
    }

    public function addJobItem(int $jobId): void
    {
        $user = auth()->user();
        $job = app(VisibleOrderQuery::class)->detail($user, $jobId);

        // Keep a single unfinished row at a time so repeated clicks cannot
        // accumulate invisible/partial product records.
        if ($job->items()->where(fn ($query) => $query
            ->whereNull('product_name')
            ->orWhere('product_name', '')
        )->exists()) {
            return;
        }

        // Add an intentionally blank draft row. The Job Details view renders
        // category, product, and quantity as editable controls immediately so
        // the user chooses the actual values instead of receiving master-data defaults.
        app(AddOrderItem::class)->handle($job, '', '', 1, $user);
    }

    public function removeJobItem(int $itemId): void
    {
        abort_unless($this->selectedJobId, 422);
        $job = app(VisibleOrderQuery::class)->detail(auth()->user(), $this->selectedJobId);
        $item = FlowJobItem::where('flow_job_id', $job->id)->findOrFail($itemId);
        app(RemoveOrderItem::class)->handle($job, $item, auth()->user());
        if ((int) ($this->editOrderProductItemId ?? 0) === $itemId) {
            $this->closeEditOrderProductModal();
        }
    }

    public function restoreJobItem(int $itemId): void
    {
        abort_unless($this->selectedJobId, 422);
        $job = app(VisibleOrderQuery::class)->detail(auth()->user(), $this->selectedJobId);
        $item = FlowJobItem::where('flow_job_id', $job->id)->findOrFail($itemId);
        app(RestoreOrderItem::class)->handle($job, $item, auth()->user());
    }

}
