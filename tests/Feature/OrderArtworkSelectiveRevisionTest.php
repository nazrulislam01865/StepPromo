<?php

namespace Tests\Feature;

use Tests\TestCase;

class OrderArtworkSelectiveRevisionTest extends TestCase
{
    public function test_artwork_review_can_preview_every_file_and_select_only_files_needing_revision(): void
    {
        $modal = file_get_contents(resource_path('views/components/jobs/order-detail/workflow-action-modal.blade.php'));
        $actions = file_get_contents(app_path('Services/OrderWorkflowActionService.php'));

        $this->assertStringContainsString('Current artwork files', $modal);
        $this->assertStringContainsString('Select a file below to preview it on the left.', $modal);
        $this->assertStringContainsString('selectedArtworkId', $modal);
        $this->assertStringContainsString('Which artwork needs revision?', $modal);
        $this->assertStringContainsString('wire:model="orderWorkflowActionPayload.revision_document_ids"', $modal);
        $this->assertStringContainsString('Select the artwork file or files that need to be replaced.', $modal);
        $this->assertStringNotContainsString("@if(\$automationKey !== 'ART_INTERNAL_REVIEW')", $modal);
        $this->assertStringContainsString("\$isArtworkRevisionRequest = \$step === 'revision';", $modal);
        $this->assertStringContainsString('class="danger ft-artwork-revision-submit"', $modal);

        $this->assertStringContainsString("'revision_document_ids' => []", $actions);
        $this->assertStringContainsString("'revision_document_ids' => \$revisionDocumentIds", $actions);
        $this->assertStringContainsString("'source_artwork_version' => \$latestVersion", $actions);
        $this->assertStringContainsString("'revision_selection_pending' => false", $actions);
        $this->assertStringContainsString('Select at least one artwork file that needs revision.', $actions);
    }

    public function test_revised_upload_replaces_selected_files_and_carries_unselected_artwork_forward(): void
    {
        $documents = file_get_contents(app_path('Services/DocumentService.php'));
        $uploadModal = file_get_contents(resource_path('views/components/jobs/order-detail/document-modal.blade.php'));
        $jobUploads = file_get_contents(app_path('Livewire/Jobs/Concerns/ManagesOrderTaskResources.php'));
        $orderUploads = file_get_contents(app_path('Livewire/Orders/Index.php'));

        $this->assertStringContainsString('public function pendingArtworkRevision(', $documents);
        $this->assertStringContainsString('public function storeArtworkRevision(', $documents);
        $this->assertStringContainsString("'retained_documents' =>", $documents);
        $this->assertStringContainsString("'event' => 'job.artwork_revision_applied'", $documents);
        $this->assertStringContainsString("'path' => \$source->path", $documents);
        $this->assertStringContainsString("'version' => \$nextVersion", $documents);

        $this->assertStringContainsString('Select artwork that needs revision', $uploadModal);
        $this->assertStringContainsString('wire:model.live="overviewTaskRevisionDocumentIds"', $uploadModal);
        $this->assertStringContainsString('Upload Selected Revision', $uploadModal);
        $this->assertStringContainsString('Unselected artwork remains unchanged in the next version.', $uploadModal);

        foreach ([$jobUploads, $orderUploads] as $component) {
            $this->assertStringContainsString('pendingArtworkRevision(', $component);
            $this->assertStringContainsString('updatePendingArtworkRevisionSelection(', $component);
            $this->assertStringContainsString('storeArtworkRevision(', $component);
            $this->assertStringContainsString("'size:'.\$revisionFileCount", $component);
        }
    }

    public function test_artwork_revision_state_is_wired_through_order_detail_components(): void
    {
        $page = file_get_contents(resource_path('views/livewire/jobs/index.blade.php'));
        $detail = file_get_contents(resource_path('views/components/jobs/detail.blade.php'));
        $overview = file_get_contents(resource_path('views/components/jobs/detail-overview.blade.php'));

        $this->assertStringContainsString(':overview-task-artwork-revision="$overviewTaskArtworkRevision ?? []"', $page);
        $this->assertStringContainsString("'overviewTaskArtworkRevision'=>[]", $detail);
        $this->assertStringContainsString(':overview-task-artwork-revision="$overviewTaskArtworkRevision"', $detail);
        $this->assertStringContainsString("'overviewTaskArtworkRevision' => []", $overview);
        $this->assertStringContainsString(':artwork-revision="$overviewTaskArtworkRevision"', $overview);
        $this->assertStringContainsString(':overview-task-revision-document-ids="$overviewTaskRevisionDocumentIds"', $detail);
        $this->assertStringContainsString(':revision-document-ids="$overviewTaskRevisionDocumentIds"', $overview);
    }

    public function test_revision_activity_card_lists_each_selected_artwork_and_resolution_uses_version(): void
    {
        $jobs = file_get_contents(app_path('Services/LegacyJobService.php'));
        $card = file_get_contents(resource_path('views/components/jobs/order-detail/artwork-revision-card.blade.php'));

        $this->assertStringContainsString("data_get(\$note->meta, 'source_artwork_version'", $jobs);
        $this->assertStringContainsString("data_get(\$note->meta, 'revision_document_ids'", $jobs);
        $this->assertStringContainsString("setRelation('revisionDocuments'", $jobs);
        $this->assertStringContainsString('Artwork selected for revision', $card);
        $this->assertStringContainsString('$revisionDocuments', $card);
        $this->assertStringContainsString('Only this selected artwork needs replacement', $card);
    }

    public function test_revision_instructions_reuse_rich_text_mentions_and_support_evidence_attachments(): void
    {
        $modal = file_get_contents(resource_path('views/components/jobs/order-detail/workflow-action-modal.blade.php'));
        $component = file_get_contents(app_path('Livewire/Jobs/Concerns/ManagesOrderWorkflow.php'));
        $actions = file_get_contents(app_path('Services/OrderWorkflowActionService.php'));
        $jobs = file_get_contents(app_path('Services/LegacyJobService.php'));
        $card = file_get_contents(resource_path('views/components/jobs/order-detail/artwork-revision-card.blade.php'));

        $this->assertStringContainsString('data-rich-text', $modal);
        $this->assertStringContainsString('data-mention-users=', $modal);
        $this->assertStringContainsString('wire:model="orderWorkflowActionAttachments"', $modal);
        $this->assertStringContainsString('data-file-dropzone', $modal);
        $this->assertStringContainsString('__flowtrackRichTextValueAsync', $modal);
        $this->assertStringContainsString('AttachmentUpload::itemRules', $component);
        $this->assertStringContainsString('storeArtworkRevisionAttachments(', $actions);
        $this->assertStringContainsString("'revision_attachment_document_ids'", $actions);
        $this->assertStringContainsString("setRelation('revisionAttachments'", $jobs);
        $this->assertStringContainsString('<x-ui.mention-text :text="$requiredChangeText" />', $card);
        $this->assertStringContainsString('Attachments from reviewer', $card);
        $this->assertStringContainsString('imageAttachments($revisionComment)', $card);
        $this->assertStringContainsString('withoutImages($revisionComment)', $card);
        $this->assertStringContainsString('<x-ui.file-type-badge', $card);
        $taskRow = file_get_contents(resource_path('views/components/jobs/order-detail/task-row.blade.php'));
        $this->assertStringContainsString('<x-ui.file-type-badge :name="$document->name"', $taskRow);
        $uploadModal = file_get_contents(resource_path('views/components/jobs/order-detail/document-modal.blade.php'));
        $this->assertStringContainsString('<x-ui.mention-text :text="$artworkRevision[\'comment\']" />', $uploadModal);
    }
}
