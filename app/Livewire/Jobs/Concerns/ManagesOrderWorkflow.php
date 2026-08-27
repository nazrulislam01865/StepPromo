<?php

namespace App\Livewire\Jobs\Concerns;

use App\Queries\Orders\VisibleOrderQuery;
use App\Models\FlowJob;
use App\Models\Task;
use App\Services\AccessControlService;
use App\Services\TaskService;

/**
 * Phase 5 Order UI workflow extracted from the legacy Jobs coordinator.
 *
 * Public method names and parent Livewire state are intentionally preserved so
 * existing Blade bindings, deep links, validation keys and realtime behavior do
 * not change during the incremental decomposition.
 */
trait ManagesOrderWorkflow
{
    public function toggleJobPhase(int $phaseId): void
    {
        if (in_array($phaseId, $this->expandedPhaseIds, true)) {
            $this->expandedPhaseIds = array_values(array_filter($this->expandedPhaseIds, fn ($id) => (int) $id !== $phaseId));
        } else {
            $this->expandedPhaseIds[] = $phaseId;
            $this->expandedPhaseIds = array_values(array_unique(array_map('intval', $this->expandedPhaseIds)));
        }
    }

    public function expandAllJobPhases(): void
    {
        if (!$this->selectedJobId) return;

        $job = app(VisibleOrderQuery::class)->scoped(
            auth()->user(),
            $this->selectedJobId,
            ['workflow.phases:id,workflow_id'],
            ['id', 'workflow_id'],
        );

        $this->expandedPhaseIds = $job->workflow->phases->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
    }

    public function collapseAllJobPhases(): void { $this->expandedPhaseIds = []; }

    public function openOrderWorkflowAction(int $taskId): void
    {
        abort_unless($this->selectedJobId && $this->detailTab === 'overview', 422);
        $task = app(TaskService::class)->visibleQuery(auth()->user())
            ->with(['job.client', 'job.items', 'job.phase', 'setupTemplate', 'documents', 'links'])
            ->where('flow_job_id', $this->selectedJobId)
            ->findOrFail($taskId);
        abort_unless(app(AccessControlService::class)->canEditTask(auth()->user(), $task), 403);
        app(\App\Services\OrderTaskSequenceService::class)->assertStatusActionable($task);

        $workflowActions = app(\App\Services\OrderWorkflowActionService::class);
        $hasEvidence = $task->documents->isNotEmpty() || $task->links->isNotEmpty();
        $descriptor = $workflowActions->descriptor($task, $hasEvidence);
        // Only file-backed prototype actions open the document picker. Other
        // actions (courier label, invoice preparation, QC, etc.) have their own
        // purpose-built interactions and must not be replaced by the generic
        // document modal just because the Task Pack also records a document
        // category. Completion requirements are still validated server-side.
        if (($descriptor['interaction'] ?? $descriptor['type'] ?? null) === 'document') {
            $this->openOverviewTaskDocumentModal($taskId);
            return;
        }

        if (($descriptor['interaction'] ?? null) === 'direct') {
            $decision = ($descriptor['key'] ?? null) === 'SHIP_LABEL' ? 'generate' : 'confirm';
            $workflowActions->perform($task, auth()->user(), $decision);
            $currentPhaseId = FlowJob::query()->whereKey($this->selectedJobId)->value('workflow_phase_id');
            if ($currentPhaseId) $this->overviewPhaseId = (int) $currentPhaseId;
            session()->flash('success', 'Order workflow updated.');
            return;
        }

        $this->orderWorkflowActionTaskId = $taskId;
        $this->orderWorkflowActionComment = '';
        $this->orderWorkflowActionStep = 'main';
        $this->orderWorkflowActionPayload = $workflowActions->initialPayload($task, $task->job);
        $this->resetValidation(['orderWorkflowActionComment', 'orderWorkflowActionPayload', 'orderWorkflowActionEmail']);
        $this->showOrderWorkflowActionModal = true;
    }

    public function closeOrderWorkflowAction(): void
    {
        $this->showOrderWorkflowActionModal = false;
        $this->orderWorkflowActionTaskId = null;
        $this->orderWorkflowActionComment = '';
        $this->orderWorkflowActionStep = 'main';
        $this->orderWorkflowActionPayload = [];
        $this->resetValidation(['orderWorkflowActionComment', 'orderWorkflowActionPayload', 'orderWorkflowActionEmail']);
    }

    public function submitOrderWorkflowAction(string $decision = 'confirm'): void
    {
        abort_unless($this->selectedJobId && $this->orderWorkflowActionTaskId, 422);
        $task = app(TaskService::class)->visibleQuery(auth()->user())
            ->with(['job.client', 'job.items', 'job.phase', 'setupTemplate'])
            ->where('flow_job_id', $this->selectedJobId)
            ->findOrFail((int) $this->orderWorkflowActionTaskId);
        abort_unless(app(AccessControlService::class)->canEditTask(auth()->user(), $task), 403);

        $workflowActions = app(\App\Services\OrderWorkflowActionService::class);
        $key = $workflowActions->automationKey($task);

        // Preserve the prototype's nested dialogs instead of collapsing every
        // action into one generic confirmation screen.
        if ($this->orderWorkflowActionStep === 'main' && $decision === 'revise'
            && in_array($key, ['ART_INTERNAL_REVIEW', 'ART_CLIENT_ERP_DECISION'], true)) {
            $this->orderWorkflowActionStep = 'revision';
            $this->orderWorkflowActionComment = '';
            $this->resetValidation(['orderWorkflowActionComment', 'orderWorkflowActionPayload', 'orderWorkflowActionEmail']);
            return;
        }
        if ($this->orderWorkflowActionStep === 'main' && $decision === 'issue'
            && in_array($key, ['PROD_ISSUE', 'QC_CHECK'], true)) {
            $this->orderWorkflowActionStep = 'issue';
            $this->orderWorkflowActionComment = '';
            $this->resetValidation(['orderWorkflowActionComment', 'orderWorkflowActionPayload', 'orderWorkflowActionEmail']);
            return;
        }

        // The prototype intentionally uses two dialogs here: first record the
        // client artwork decision, then ask whether a sample/swatch is required.
        if ($key === 'ART_CLIENT_ERP_DECISION' && $decision === 'approved' && $this->orderWorkflowActionStep === 'main') {
            $this->orderWorkflowActionStep = 'sample';
            $this->orderWorkflowActionComment = '';
            $this->resetValidation(['orderWorkflowActionComment', 'orderWorkflowActionPayload', 'orderWorkflowActionEmail']);
            return;
        }
        if ($key === 'ART_CLIENT_ERP_DECISION' && $this->orderWorkflowActionStep === 'sample') {
            $decision = $decision === 'sample_yes' ? 'sample' : 'confirm';
        }

        $workflowActions->perform(
            $task,
            auth()->user(),
            $decision,
            $this->orderWorkflowActionComment,
            $this->orderWorkflowActionPayload,
        );

        $successMessage = match ($key) {
            'NEW_SEND_PO_ARTWORK' => 'Purchase Order emailed to the Artwork Team.',
            'ART_SEND_ORDER_TEAM' => 'Artwork emailed to the Order Team.',
            default => 'Order workflow updated.',
        };

        $this->closeOrderWorkflowAction();
        $currentPhaseId = FlowJob::query()->whereKey($this->selectedJobId)->value('workflow_phase_id');
        if ($currentPhaseId) $this->overviewPhaseId = (int) $currentPhaseId;
        session()->flash('success', $successMessage);
    }

}
