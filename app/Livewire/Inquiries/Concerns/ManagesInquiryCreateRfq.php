<?php

namespace App\Livewire\Inquiries\Concerns;

use App\Models\Inquiry;
use App\Models\User;
use App\Services\Inquiries\InquiryRfqService;
use App\Services\MasterDataService;
use App\Services\WorkspaceSettingsService;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Keeps Create Inquiry RFQ state and delivery isolated from the main creation
 * workflow. The create page can therefore opt into RFQ invitations without
 * coupling supplier selection to product assignment or Inquiry persistence.
 */
trait ManagesInquiryCreateRfq
{
    private function initialiseCreateRfqState(): void
    {
        if ($this->createRfqDueDate === '') {
            $this->createRfqDueDate = app(WorkspaceSettingsService::class)
                ->localToday()
                ->addDays(7)
                ->toDateString();
        }

        if (trim($this->createRfqMessage) === '') {
            $this->createRfqMessage = $this->defaultCreateRfqMessage();
        }
    }

    private function resetCreateRfqState(): void
    {
        $this->createRfqSupplierSearch = '';
        $this->createRfqSupplierIds = [];
        $this->createRfqDueDate = app(WorkspaceSettingsService::class)
            ->localToday()
            ->addDays(7)
            ->toDateString();
        $this->createRfqMessage = $this->defaultCreateRfqMessage();
    }

    /**
     * Validate supplier IDs against the same RFQ service used by the detail page.
     * This prevents stale/inactive suppliers or records without email addresses
     * from being injected into the Livewire payload.
     *
     * @return array<int,int>
     */
    private function validatedCreateRfqSupplierIds(): array
    {
        $ids = collect($this->createRfqSupplierIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return [];
        }

        $workspaceId = app(MasterDataService::class)->workspaceId();
        $available = app(InquiryRfqService::class)
            ->invitableSuppliersByIds($workspaceId, $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $expected = collect($ids)->sort()->values()->all();
        if ($available !== $expected) {
            $this->addError('createRfqSupplierIds', 'One or more selected suppliers are no longer active or do not have a valid email address.');
            return [];
        }

        return $ids;
    }

    /**
     * Send invitations after the Inquiry and its products/tasks/documents exist.
     * External email delivery is deliberately not wrapped in the Inquiry database
     * transaction; one provider failure must not roll back an already-created
     * Inquiry or duplicate messages that were accepted successfully.
     *
     * @param array<int,int> $supplierIds
     * @return array{sent:int,failed:int}
     */
    private function sendCreateRfqInvitations(Inquiry $inquiry, User $actor, array $supplierIds): array
    {
        if ($supplierIds === []) {
            return ['sent' => 0, 'failed' => 0];
        }

        $dueAt = $this->createRfqDueDate !== ''
            ? Carbon::createFromFormat('Y-m-d', $this->createRfqDueDate)->endOfDay()
            : null;
        $message = trim($this->createRfqMessage);
        $sent = 0;
        $failed = 0;

        foreach ($supplierIds as $supplierId) {
            try {
                app(InquiryRfqService::class)->invite(
                    $inquiry,
                    (int) $supplierId,
                    $actor,
                    $dueAt,
                    $message !== '' ? $message : null,
                );
                $sent++;
            } catch (Throwable $exception) {
                report($exception);
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    private function defaultCreateRfqMessage(): string
    {
        return 'Please quote your best unit price, lead time, shipping and sample options.';
    }
}
