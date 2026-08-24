<?php

namespace App\Actions\Orders;

use App\Models\Invoice;
use App\Models\User;
use App\Queries\Orders\VisibleOrderQuery;
use App\Services\AccessControlService;
use Illuminate\Support\Facades\Mail;

/** Send the existing Order invoice email and mark the invoice as emailed. */
final class EmailOrderInvoice
{
    public function __construct(
        private readonly VisibleOrderQuery $orders,
        private readonly AccessControlService $access,
    ) {
    }

    public function handle(User $actor, Invoice $invoice): void
    {
        $job = $this->orders->base($actor, (int) $invoice->flow_job_id);
        abort_unless($this->access->can($actor, 'finance', 'create'), 403);

        if (! $invoice->billing_contact_email) return;

        $job->loadMissing('client');
        $subject = 'Invoice '.$invoice->invoice_number.' · '.($job->displayOrderNumber() ?? 'Order');
        $html = '<p>Hello '.e($invoice->billing_contact_name ?: 'there').',</p>'
            .'<p>Invoice <strong>'.e($invoice->invoice_number).'</strong> has been created for '.e($job->title ?: 'your order').'.</p>'
            .'<p><strong>Amount:</strong> '.e($invoice->currency).' '.number_format((float) $invoice->total, 2).'<br>'
            .'<strong>Due date:</strong> '.e($invoice->due_date?->format('M j, Y') ?: '—').'</p>'
            .'<p>'.nl2br(e((string) ($invoice->notes ?: 'Please include the invoice number with your payment.'))).'</p>';

        Mail::html($html, fn ($message) => $message->to($invoice->billing_contact_email)->subject($subject));
        $invoice->update(['emailed_at' => now()]);
    }
}
