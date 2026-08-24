<?php

namespace App\Livewire\Inquiries\Concerns;

use App\Models\Inquiry;
use App\Models\InquiryTask;
use App\Models\MasterRecord;
use App\Models\Document;
use App\Models\User;
use App\Services\AccessControlService;
use App\Services\MentionService;
use App\Services\MasterDataService;
use Throwable;

trait BuildsInquiryPageData
{
    private function listPageData(User $user): array
    {
        $listQuery = app(\App\Queries\Inquiries\InquiryListQuery::class);
        $selectedClientId = $this->listClient !== '' ? (int) $this->listClient : null;
        $paginator = $listQuery->paginate($user, [
            'search' => $this->search,
            'quick' => $this->quick,
            'metric_filter' => $this->metricFilter,
            'client_id' => $selectedClientId,
            'status' => $this->listStatus,
            'hide_completed' => $this->hideCompleted,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ], self::INQUIRIES_PER_PAGE);
        $listClientFilterOptions = app(\App\Services\FilterOptionService::class)
            ->options($user, 'clients', 'inquiries', '', $selectedClientId, 6);
        $detailQuery = app(\App\Queries\Inquiries\InquiryDetailQuery::class);
        $rows = $listQuery->rows($paginator, $user)->map(function (array $row) use ($detailQuery): array {
            $row['statusColor'] = $detailQuery->statusColor(
                (string) ($row['status'] ?? ''),
                (string) ($row['taskStatus'] ?? ''),
            );
            return $row;
        });

        return [
            'mode' => 'list',
            'inquiryPaginator' => $paginator,
            'inquiryRows' => $rows,
            'listClientFilterOptions' => $listClientFilterOptions,
            'listStatusOptions' => $listQuery->taskStatusOptions(),
            'selectedInquiry' => null,
            'selectedTask' => null,
        ];
    }

    private function createPageData(): array
    {
        $user = auth()->user();
        $workspaceId = app(MasterDataService::class)->workspaceId();
        $canUseInquiryProductSelector = $this->canUseCreateInquiryProducts($user);
        $canViewProductCategories = $user->canModule('product_categories', 'view');
        $productCategories = collect();
        $productSearchResults = collect();
        $selectedProductDetails = collect();
        $activeProductCount = 0;
        $productResultTotal = 0;

        if ($canUseInquiryProductSelector) {
            if ($canViewProductCategories) {
                $productCategories = MasterRecord::query()
                    ->forWorkspace($workspaceId)
                    ->ofType('product_category')
                    ->active()
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get(['id', 'name', 'code']);
            }

            $catalog = app(\App\Services\ProductCatalogService::class);
            $activeProductCount = $catalog->activeCount();
            $search = trim($this->createProductSearch);
            $categoryFilterId = $canViewProductCategories && ctype_digit(trim($this->createProductCategoryFilter))
                ? (int) $this->createProductCategoryFilter
                : 0;
            $productResultTotal = $catalog->orderSearchCount($search, $categoryFilterId ?: null);
            $resultLimit = $this->createProductShowAllResults || $productResultTotal <= 20 ? 20 : 3;
            $productSearchResults = $catalog->searchForOrderCreation($search, $categoryFilterId ?: null, $resultLimit);
            $selectedProductDetails = $catalog->selectedProducts(collect($this->createProductRows)->pluck('product_id'));
        }

        $duplicateProduct = null;
        $newProductCategoryMatches = collect();
        $newProductSimilarCategories = collect();
        $newProductSimilarProducts = collect();
        $newProductSelectedCategory = null;
        $newProductHasExactCategory = false;
        $newProductImagePreview = null;

        if ($canUseInquiryProductSelector && $this->showCreateOrderProductModal) {
            $code = trim($this->newProductCode);
            if ($code !== '') {
                $duplicateProduct = MasterRecord::withTrashed()
                    ->forWorkspace($workspaceId)
                    ->ofType('product')
                    ->with('parent:id,name,status')
                    ->whereRaw('LOWER(code) = ?', [mb_strtolower($code)])
                    ->first(['id', 'type', 'parent_id', 'name', 'code', 'metadata', 'status']);
            }

            $categorySearch = trim($this->newProductCategorySearch);
            if ($canViewProductCategories) {
                $newProductCategoryMatches = MasterRecord::query()
                ->forWorkspace($workspaceId)
                ->ofType('product_category')
                ->active()
                ->when($categorySearch !== '', fn ($query) => $query->whereLike('name', '%'.$categorySearch.'%'))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(6)
                ->get(['id', 'name', 'code']);

            if ($categorySearch !== '') {
                $newProductHasExactCategory = MasterRecord::query()
                    ->forWorkspace($workspaceId)
                    ->ofType('product_category')
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($categorySearch)])
                    ->exists();

                $tokens = collect(preg_split('/\s+/', $categorySearch) ?: [])
                    ->map(fn ($token) => trim($token))
                    ->filter(fn ($token) => mb_strlen($token) >= 3)
                    ->take(3)
                    ->values();

                if ($tokens->isNotEmpty()) {
                    $newProductSimilarCategories = MasterRecord::query()
                        ->forWorkspace($workspaceId)
                        ->ofType('product_category')
                        ->active()
                        ->where(function ($query) use ($tokens) {
                            foreach ($tokens as $token) $query->orWhereLike('name', '%'.$token.'%');
                        })
                        ->when($newProductCategoryMatches->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $newProductCategoryMatches->pluck('id')))
                        ->orderBy('name')
                        ->limit(2)
                        ->get(['id', 'name', 'code']);
                }
            }

                if ($this->newProductCategoryId) {
                    $newProductSelectedCategory = MasterRecord::query()
                        ->forWorkspace($workspaceId)
                        ->ofType('product_category')
                        ->active()
                        ->find($this->newProductCategoryId, ['id', 'name', 'code']);
                }
            }

            $nameSearch = trim($this->newProductName);
            if (mb_strlen($nameSearch) >= 3) {
                $newProductSimilarProducts = MasterRecord::query()
                    ->forWorkspace($workspaceId)
                    ->ofType('product')
                    ->active()
                    ->with('parent:id,name,status')
                    ->whereLike('name', '%'.$nameSearch.'%')
                    ->when($duplicateProduct, fn ($query) => $query->whereKeyNot($duplicateProduct->id))
                    ->orderBy('name')
                    ->limit(3)
                    ->get(['id', 'type', 'parent_id', 'name', 'code', 'metadata', 'status']);
            }

            if ($this->newProductImage) {
                try {
                    $newProductImagePreview = $this->newProductImage->temporaryUrl();
                } catch (Throwable) {
                    $newProductImagePreview = null;
                }
            }
        }

        return [
            'mode' => 'create',
            'selectedInquiry' => null,
            'selectedTask' => null,
            'catalogReady' => $canUseInquiryProductSelector,
            'canUseInquiryProductSelector' => $canUseInquiryProductSelector,
            'productCategories' => $productCategories,
            'productSearchResults' => $productSearchResults,
            'selectedProductDetails' => $selectedProductDetails,
            'activeProductCount' => $activeProductCount,
            'productResultTotal' => $productResultTotal,
            'canCreateCatalogProduct' => $canUseInquiryProductSelector && $user->canModule('catalog_products', 'create'),
            'canViewProductCategories' => $canViewProductCategories,
            'canCreateProductCategory' => $canViewProductCategories && $user->canModule('product_categories', 'create'),
            'duplicateProduct' => $duplicateProduct,
            'newProductCategoryMatches' => $newProductCategoryMatches,
            'newProductSimilarCategories' => $newProductSimilarCategories,
            'newProductSimilarProducts' => $newProductSimilarProducts,
            'newProductSelectedCategory' => $newProductSelectedCategory,
            'newProductHasExactCategory' => $newProductHasExactCategory,
            'newProductImagePreview' => $newProductImagePreview,
            'createPriorityOptions' => app(MasterDataService::class)->active('priority'),
        ];
    }

    private function detailPageData(User $user): array
    {
        $detailQuery = app(\App\Queries\Inquiries\InquiryDetailQuery::class);
        $access = app(AccessControlService::class);
        $canViewInquiryProducts = $access->can($user, 'catalog_products', 'view');
        $canViewTasks = $user->canModule('tasks', 'view')
            || Inquiry::query()->whereKey($this->selectedInquiryId)->where('created_by', $user->id)->exists();
        $with = [
            'client:id,name,logo_path',
            'creator:id,name,profile_image_path',
            'owner:id,name,profile_image_path',
            'convertedJob:id,job_number,order_number',
            'sourceWorkflow:id,name',
            'currentTask:id,inquiry_id,assignee_id,title,due_date,status,needs_attention,attention_reason,started_at,completed_at',
            'currentTask.assignee:id,name,profile_image_path',
        ];
        if ($canViewInquiryProducts) {
            $with[] = 'items:id,inquiry_id,category,item_name,quantity,unit,unit_price,notes,sort_order,created_at,updated_at';
        }
        if ($this->detailTab === 'overview' && $canViewTasks) {
            // Overview owns the fully interactive Inquiry Taskflow. Load only tasks allowed by the Tasks matrix.
            $with['tasks'] = fn ($query) => app(AccessControlService::class)->applyInquiryTaskScope($query, $user)
                ->with([
                    'assignee:id,name,profile_image_path',
                    'sourceTaskPackItem:id,color',
                    'documents:id,inquiry_id,inquiry_task_id,name,note,mime_type,created_at',
                    'links:id,inquiry_task_id,url,created_at',
                ])
                ->withCount(['documents', 'comments'])
                ->orderBy('sequence');
        }

        $inquiry = $detailQuery->detail($user, (int) $this->selectedInquiryId, $with, [
            'tasks',
            'tasks as completed_tasks_count' => fn ($q) => $q->whereNotNull('completed_at'),
            'documents',
        ]);

        if (!$canViewTasks) {
            $inquiry->setRelation('tasks', collect());
            $inquiry->setRelation('currentTask', null);
            $inquiry->setAttribute('tasks_count', 0);
            $inquiry->setAttribute('completed_tasks_count', 0);
        }

        $inquiryProductMasters = collect();
        if ($canViewInquiryProducts) {
            $productNames = $inquiry->items
                ->pluck('item_name')
                ->filter(fn ($name) => filled($name))
                ->unique()
                ->values();

            if ($productNames->isNotEmpty()) {
                $inquiryProductMasters = \App\Models\MasterRecord::query()
                    ->where('workspace_id', max(1, (int) config('flowtrack.workspace_id', 1)))
                    ->where('type', 'product')
                    ->whereIn('name', $productNames)
                    ->with('parent')
                    ->get()
                    ->keyBy(fn ($record) => mb_strtolower(trim((string) $record->name)));
            }
        }

        $inquiryCurrency = strtoupper((string) ($inquiry->currency ?: 'USD'));
        $inquiryCurrencySymbol = match ($inquiryCurrency) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CNY', 'RMB' => '¥',
            default => $inquiryCurrency.' ',
        };

        $inquiryProductSearchResults = collect();
        $inquiryProductResultTotal = 0;
        $inquiryProductSelectedProduct = null;
        if ($this->detailTab === 'overview' && $this->showAddInquiryProductForm && $canViewInquiryProducts) {
            $catalog = app(\App\Services\ProductCatalogService::class);
            $productSearch = trim($this->inquiryProductSearch);
            $inquiryProductResultTotal = $catalog->orderSearchCount($productSearch, null);
            $resultLimit = $this->inquiryProductShowAllResults || $inquiryProductResultTotal <= 20 ? 20 : 3;
            $inquiryProductSearchResults = $catalog->searchForOrderCreation($productSearch, null, $resultLimit);
            if ($this->inquiryProductSelectedId) {
                $inquiryProductSelectedProduct = $catalog->selectedProducts([$this->inquiryProductSelectedId])->first();
            }
        }

        // Documents and Activity remain part of Overview, but no longer have separate tabs.
        $documents = $this->detailTab === 'overview' && $user->canModule('documents', 'view') ? $detailQuery->documents($user, $inquiry) : null;
        $activities = $this->detailTab === 'overview' ? $detailQuery->activity($user, $inquiry, 30, $this->inquiryActivityTab) : null;
        $mentionUsers = $this->detailTab === 'overview' ? app(MentionService::class)->optionsForCreate($user) : collect();
        $availableInquiryDocuments = $this->showInquiryDocumentPicker && $this->detailTab === 'overview'
            ? app(AccessControlService::class)->applyDocumentScope(Document::query(), $user)
                ->where('client_id', $inquiry->client_id)
                ->latest('id')
                ->limit(60)
                ->get(['id','name','flow_job_id','task_id','client_id'])
            : collect();
        $taskDocumentModalTask = $this->showTaskDocumentModal && $this->taskDocumentModalTaskId
            ? $inquiry->tasks->firstWhere('id', (int) $this->taskDocumentModalTaskId)
            : null;
        $availableTaskDocuments = $this->showTaskDocumentModal
            && $this->taskDocumentSource === 'existing'
            && $user->canModule('documents', 'link')
            ? app(AccessControlService::class)->applyDocumentScope(Document::query(), $user)
                ->where('client_id', $inquiry->client_id)
                ->latest('id')
                ->limit(80)
                ->get(['id', 'name', 'flow_job_id', 'task_id', 'client_id', 'size', 'mime_type'])
            : collect();
        // Inquiry task management is inline on the workflow tab. Avoid loading
        // task documents/comments into a modal on every task deep-link/render.
        $task = null;
        $canEditInquiry = $detailQuery->canEditVisible($user, $inquiry);
        $canManageInquiryRecord = $canEditInquiry && ! $inquiry->result;
        // Keep overview editing aligned with the same current-task rule used by
        // the Inquiry list: furthest started open task, otherwise first queued.
        $activeTask = $inquiry->currentTask ?: $inquiry->tasks->first(fn (InquiryTask $row) => !$row->completed_at);
        $canEditActiveTask = $activeTask ? $detailQuery->canEditTask($user, $activeTask) : false;
        $workflowQuery = app(\App\Queries\Inquiries\InquiryWorkflowQuery::class);
        $inquiryTaskUi = [];
        foreach ($inquiry->tasks as $taskRow) {
            $inquiryTaskUi[(int) $taskRow->id] = [
                'canEdit' => $detailQuery->canEditTask($user, $taskRow),
                'statusNeedsAttention' => $detailQuery->taskStatusNeedsAttention((string) $taskRow->status),
            ];
        }
        $inquiryTaskStatusCompletion = [];
        $taskStatusNames = $detailQuery->taskStatusOptions()
            ->merge($inquiry->tasks->pluck('status'))
            ->filter()
            ->unique(fn ($status) => mb_strtolower(trim((string) $status)));
        foreach ($taskStatusNames as $taskStatusName) {
            $name = (string) $taskStatusName;
            $inquiryTaskStatusCompletion[$name] = $workflowQuery->isCompletionStatus($name);
        }

        return [
            'mode' => 'detail',
            'selectedInquiry' => $inquiry,
            'selectedTask' => $task,
            'inquiryDocuments' => $documents,
            'inquiryActivities' => $activities,
            'inquiryMentionUsers' => $mentionUsers,
            'availableInquiryDocuments' => $availableInquiryDocuments,
            'taskDocumentModalTask' => $taskDocumentModalTask,
            'availableTaskDocuments' => $availableTaskDocuments,
            'canLinkDocuments' => $user->canModule('documents', 'link'),
            'canCreateDocuments' => $user->canModule('documents', 'create'),
            'canDeleteDocuments' => $user->canModule('documents', 'delete'),
            'canExportDocuments' => $user->canModule('documents', 'export'),
            'canAssignInquiry' => $user->canModule('inquiries', 'assign') || app(AccessControlService::class)->isInquiryCreator($user, $inquiry),
            'canEditInquiry' => $canEditInquiry,
            'canViewInquiryProducts' => $canViewInquiryProducts,
            'canCreateInquiryProducts' => $canManageInquiryRecord && $canViewInquiryProducts && $access->can($user, 'catalog_products', 'create'),
            'canEditInquiryProducts' => $canManageInquiryRecord && $canViewInquiryProducts && $access->can($user, 'catalog_products', 'edit'),
            'canDeleteInquiryProducts' => $canManageInquiryRecord && $canViewInquiryProducts && $access->can($user, 'catalog_products', 'delete'),
            'inquiryProductMasters' => $inquiryProductMasters,
            'inquiryCurrencySymbol' => $inquiryCurrencySymbol,
            'inquiryProductSearchResults' => $inquiryProductSearchResults,
            'inquiryProductResultTotal' => $inquiryProductResultTotal,
            'inquiryProductSelectedProduct' => $inquiryProductSelectedProduct,
            'canEditActiveTask' => $canEditActiveTask,
            'detailStatusColor' => $detailQuery->statusColor((string) $inquiry->status, (string) ($activeTask?->status ?: '')),
            'inquiryTaskUi' => $inquiryTaskUi,
            'inquiryTaskStatusCompletion' => $inquiryTaskStatusCompletion,
            'inquiryDefaultTaskStatus' => $workflowQuery->defaultTaskStatus(),
            'inquiryDefaultStatus' => $workflowQuery->defaultInquiryStatus(),
            'canAddInquiryTask' => app(AccessControlService::class)->canCreateInquiryTask($user, $inquiry) && !$inquiry->result,
            'inquiryPriorities' => $this->detailTab === 'overview' ? app(\App\Services\MasterDataService::class)->active('priority') : collect(),
            'inquiryTaskStatusOptions' => $this->detailTab === 'overview' ? $detailQuery->taskStatusOptions() : collect(),
            'canCreateOrder' => $user->canModule('jobs', 'create'),
            'selectedTaskIsActive' => false,
            'selectedTaskCanEdit' => false,
        ];
    }
}
