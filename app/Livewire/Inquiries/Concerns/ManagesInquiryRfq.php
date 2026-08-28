<?php

namespace App\Livewire\Inquiries\Concerns;

use App\Queries\Inquiries\InquiryDetailQuery;
use App\Services\Inquiries\InquiryRfqService;
use Throwable;

trait ManagesInquiryRfq
{
    public string $rfqSupplierSearch = '';
    public bool $showRfqEmailPreview = false;
    public string $rfqEmailPreviewType = 'invitation';

    public function resetInquiryRfqState(): void
    {
        $this->rfqSupplierSearch = '';
        $this->showRfqEmailPreview = false;
        $this->rfqEmailPreviewType = 'invitation';
        $this->resetValidation();
    }

    public function inviteRfqSupplier(int $supplierId): void
    {
        $inquiry = $this->selectedInquiry();
        abort_unless(app(InquiryDetailQuery::class)->canEditVisible(auth()->user(), $inquiry) && ! $inquiry->result, 403);

        try {
            $invitation = app(InquiryRfqService::class)->invite($inquiry, $supplierId, auth()->user());
            $this->rfqSupplierSearch = '';
            $this->resetPage('inquiryActivityPage');
            if ($invitation->email_status === 'Delivered') {
                session()->flash('success', 'RFQ invitation sent to '.$invitation->supplier?->name.'.');
            } elseif ($invitation->email_status === 'Email disabled') {
                session()->flash('success', ($invitation->supplier?->name ?: 'Supplier').' added to the RFQ. Inquiry email service is disabled, so no email was sent.');
            } else {
                session()->flash('success', ($invitation->supplier?->name ?: 'Supplier').' added to the RFQ. No email was sent because the supplier has no configured email address.');
            }
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('rfqSupplierSearch', $exception->getMessage() ?: 'The RFQ invitation could not be sent.');
        }
    }

    public function sendExistingRfqInvitation(int $invitationId): void
    {
        $inquiry = $this->selectedInquiry();
        abort_unless(app(InquiryDetailQuery::class)->canEditVisible(auth()->user(), $inquiry) && ! $inquiry->result, 403);

        try {
            $invitation = app(InquiryRfqService::class)->sendExistingInvitation($inquiry, $invitationId, auth()->user());
            $this->resetPage('inquiryActivityPage');
            session()->flash('success', 'RFQ invitation sent to '.$invitation->supplier?->name.'.');
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('rfqSupplierSearch', $exception->getMessage() ?: 'The RFQ invitation could not be sent.');
        }
    }

    public function awardRfqSupplier(int $invitationId): void
    {
        $inquiry = $this->selectedInquiry();
        abort_unless(app(InquiryDetailQuery::class)->canEditVisible(auth()->user(), $inquiry) && ! $inquiry->result, 403);

        try {
            $result = app(InquiryRfqService::class)->award($inquiry, $invitationId, auth()->user());
            $name = $result['winner']->supplier?->name ?: 'Supplier';
            $failures = (int) ($result['email_failures'] ?? 0);
            $emailDisabled = (bool) ($result['email_service_disabled'] ?? false);
            $deliveryMessage = $emailDisabled
                ? ' Inquiry email service is disabled, so supplier notification emails were skipped.'
                : ($failures ? ' '.$failures.' notification email(s) could not be delivered.' : ' Supplier emails were sent.');
            session()->flash('success', $name.' was awarded the quotation.'.$deliveryMessage);
            $this->resetPage('inquiryActivityPage');
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('rfqAward', $exception->getMessage() ?: 'The quotation could not be awarded.');
        }
    }

    public function openRfqEmailPreview(string $type = 'invitation'): void
    {
        $this->rfqEmailPreviewType = in_array($type, ['invitation','reminder','received','award','not_selected'], true) ? $type : 'invitation';
        $this->showRfqEmailPreview = true;
    }

    public function closeRfqEmailPreview(): void
    {
        $this->showRfqEmailPreview = false;
    }

    public function setRfqEmailPreviewType(string $type): void
    {
        abort_unless(in_array($type, ['invitation','reminder','received','award','not_selected'], true), 422);
        $this->rfqEmailPreviewType = $type;
    }
}
