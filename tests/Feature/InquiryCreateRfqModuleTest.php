<?php

namespace Tests\Feature;

use Tests\TestCase;

class InquiryCreateRfqModuleTest extends TestCase
{
    public function test_create_inquiry_uses_modular_rfq_selector_and_shared_supplier_service(): void
    {
        $create = file_get_contents(resource_path('views/livewire/inquiries/sections/create.blade.php'));
        $component = file_get_contents(resource_path('views/components/inquiries/create-rfq.blade.php'));
        $choice = file_get_contents(resource_path('views/components/inquiries/rfq-supplier-choice.blade.php'));
        $pageData = file_get_contents(app_path('Livewire/Inquiries/Concerns/BuildsInquiryPageData.php'));
        $rfq = file_get_contents(app_path('Services/Inquiries/InquiryRfqService.php'));

        $this->assertStringContainsString('<x-inquiries.create-rfq', $create);
        $this->assertStringContainsString('Invite suppliers to the RFQ', $component);
        $this->assertStringContainsString('<x-inquiries.rfq-supplier-choice', $component);
        $this->assertStringContainsString('wire:model.live="{{ $model }}"', $choice);
        $this->assertStringContainsString('wire:model="createRfqDueDate"', $component);
        $this->assertStringContainsString('wire:model="createRfqMessage"', $component);
        $this->assertStringContainsString('Suppliers remain RFQ participants only.', $component);
        $this->assertStringContainsString('supplierChoicesForWorkspace', $pageData);
        $this->assertStringContainsString('public function candidateSuppliersForWorkspace', $rfq);
        $this->assertStringContainsString('public function supplierChoicesForWorkspace', $rfq);
        $this->assertStringContainsString("'invitable' => \$invitable", $rfq);
        $this->assertStringContainsString('@disabled(! $invitable)', $choice);
        $this->assertStringContainsString('public function invitableSuppliersByIds', $rfq);
    }

    public function test_create_inquiry_sends_selected_rfq_invitations_only_after_creation(): void
    {
        $creation = file_get_contents(app_path('Livewire/Inquiries/Concerns/ManagesInquiryCreation.php'));
        $createRfq = file_get_contents(app_path('Livewire/Inquiries/Concerns/ManagesInquiryCreateRfq.php'));
        $email = file_get_contents(resource_path('views/emails/rfq/invitation.blade.php'));
        $model = file_get_contents(app_path('Models/InquiryRfqInvitation.php'));

        $createPosition = strpos($creation, 'CreateInquiry::class)->handle');
        $sendPosition = strpos($creation, 'sendCreateRfqInvitations');

        $this->assertNotFalse($createPosition);
        $this->assertNotFalse($sendPosition);
        $this->assertGreaterThan($createPosition, $sendPosition);
        $this->assertStringContainsString('if (! $draft && $createRfqSupplierIds !== [])', $creation);
        $this->assertStringContainsString('InquiryRfqService::class)->invite(', $createRfq);
        $this->assertStringContainsString('request_message', $model);
        $this->assertStringContainsString('filled($requestMessage ?? null)', $email);
    }

    public function test_create_rfq_css_is_isolated_in_its_own_application_module(): void
    {
        $core = file_get_contents(resource_path('css/application/core.css'));
        $css = file_get_contents(resource_path('css/modules/application/21-inquiry-create-rfq.css'));

        $this->assertStringContainsString("@import '../modules/application/21-inquiry-create-rfq.css';", $core);
        $this->assertStringContainsString('.ft-create-rfq-layout', $css);
        $this->assertStringContainsString('.ft-create-rfq-card--settings', $css);
        $this->assertStringContainsString('.ft-create-rfq-supplier.is-selected', $css);
    }
}
