@props(['job', 'task', 'availableDocuments' => collect(), 'source' => 'upload', 'upload' => null, 'existingDocumentId' => null, 'artworkRevision' => [], 'revisionDocumentIds' => [], 'context' => []])
@php
    $canUpload = (bool) ($context['canUploadDocument'] ?? false);
    $canLink = (bool) ($context['canLinkDocument'] ?? false);
    $workflowActions = app(\App\Services\OrderWorkflowActionService::class);
    $automationKey = $workflowActions->automationKey($task);
    $prototypeUpload = in_array($automationKey, ['NEW_UPLOAD_PO', 'ART_PREPARE_UPLOAD', 'ART_SAMPLE_APPROVAL'], true);
    $allowMultipleUploads = $automationKey === 'ART_PREPARE_UPLOAD'
        || (bool) ($task->setupTemplate?->allow_multiple_documents ?? false);
    $hasExistingEvidence = $task->relationLoaded('documents') ? $task->documents->isNotEmpty() : false;
    $artworkRevisionActive = $automationKey === 'ART_PREPARE_UPLOAD' && (bool) ($artworkRevision['active'] ?? false);
    $allRevisionCandidates = $artworkRevisionActive
        ? collect($artworkRevision['documents'] ?? [])->merge(collect($artworkRevision['retained_documents'] ?? []))->unique('id')->sortBy('id')->values()
        : collect();
    $selectedRevisionIds = collect($revisionDocumentIds ?: ($artworkRevision['document_ids'] ?? []))
        ->map(fn($id) => (int) $id)
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values();
    $revisionDocuments = $artworkRevisionActive
        ? $allRevisionCandidates->filter(fn($document) => $selectedRevisionIds->contains((int) $document->id))->values()
        : collect();
    $retainedArtworkDocuments = $artworkRevisionActive
        ? $allRevisionCandidates->reject(fn($document) => $selectedRevisionIds->contains((int) $document->id))->values()
        : collect();
    $revisionCount = $revisionDocuments->count();
    // Keep Artwork bound to a multiple-file input because Livewire stores this
    // field in an array property even when a selective revision needs one file.
    $inputAllowsMultiple = $allowMultipleUploads;
    $uploadCopyPlural = $artworkRevisionActive ? $revisionCount > 1 : $allowMultipleUploads;

    $prototypeConfig = match ($automationKey) {
        'NEW_UPLOAD_PO' => [
            'title' => 'Upload Purchase Order',
            'label' => 'Purchase order file',
            'copy' => 'Upload the client purchase order to begin processing this Order.',
            'hint' => \App\Support\AttachmentUpload::helperText(20),
            'button' => 'Upload Purchase Order',
        ],
        'ART_PREPARE_UPLOAD' => [
            'title' => $hasExistingEvidence ? 'Upload Revised Artwork' : 'Upload Artwork',
            'label' => $artworkRevisionActive ? 'Replacement artwork files' : ($hasExistingEvidence ? 'Revised artwork files' : 'Artwork files'),
            'copy' => $artworkRevisionActive
                ? ($revisionCount > 0
                    ? 'Upload only the '.($revisionCount === 1 ? 'artwork file selected' : $revisionCount.' artwork files selected').' for revision. Unselected artwork remains unchanged automatically.'
                    : 'Select the artwork that needs revision, then upload one replacement for each selected file. Unselected artwork remains unchanged automatically.')
                : ($hasExistingEvidence
                    ? 'Upload up to 10 corrected artwork files as one revision. The previous version remains in Order history.'
                    : 'Upload up to 10 artwork files together for internal review.'),
            'hint' => $artworkRevisionActive
                ? ($revisionCount > 0
                    ? \App\Support\AttachmentUpload::helperText(20).' · Exactly '.$revisionCount.' replacement'.($revisionCount === 1 ? '' : 's').' required'
                    : 'Select artwork above first · '.\App\Support\AttachmentUpload::helperText(20))
                : \App\Support\AttachmentUpload::helperText(20).' · Up to 10 files',
            'button' => $artworkRevisionActive ? 'Upload Selected Revision' : ($hasExistingEvidence ? 'Upload Revised Artwork' : 'Upload Artwork'),
        ],
        'ART_SAMPLE_APPROVAL' => [
            'title' => 'Upload Sample Approval',
            'label' => 'Signed sample approval',
            'copy' => 'Attach the client sample/swatch approval to continue to Production.',
            'hint' => \App\Support\AttachmentUpload::helperText(20),
            'button' => 'Upload Sample Approval',
        ],
        default => [
            'title' => 'Add document to task',
            'label' => 'Task document',
            'copy' => 'Upload a new file or link an existing client document.',
            'hint' => \App\Support\AttachmentUpload::helperText(20),
            'button' => 'Add document',
        ],
    };
    $selectedUploads = collect(is_array($upload) ? $upload : ($upload ? [$upload] : []))->filter()->values();
    $selectedUploadCount = $selectedUploads->count();
    $selectedUploadDetails = $selectedUploads->map(function ($file) {
        $name = $file->getClientOriginalName();

        return [
            'name' => $name,
            'type' => strtoupper((string) pathinfo($name, PATHINFO_EXTENSION)) ?: 'FILE',
            'size' => $file->getSize() >= 1048576
                ? number_format($file->getSize() / 1048576, 1).' MB'
                : number_format(max(1, (int) ceil($file->getSize() / 1024))).' KB',
        ];
    });
@endphp
<div class="ft-order-task-document-modal-backdrop" wire:key="order-task-document-modal-{{ $task->id }}" wire:click.self="closeOverviewTaskDocumentModal">
    <section class="ft-order-task-document-modal ft-order-attachment-upload-modal {{ $prototypeUpload ? 'ft-order-prototype-upload-modal' : '' }} {{ $artworkRevisionActive ? 'ft-order-prototype-upload-modal--artwork-revision' : '' }}" data-ft-feedback-scope="form" role="dialog" aria-modal="true" aria-labelledby="order-task-document-modal-title">
        <header class="ft-order-task-document-modal-head">
            <div>
                <h2 id="order-task-document-modal-title">{{ $prototypeConfig['title'] }}</h2>
                <p>{{ $prototypeConfig['copy'] }}</p>
            </div>
            <button type="button" wire:click="closeOverviewTaskDocumentModal" aria-label="Close">×</button>
        </header>
        <div class="ft-order-task-document-modal-body">
            @unless($prototypeUpload)
                <div class="ft-order-task-document-target"><span class="ft-order-task-document-target-icon">▣</span><div><small>ATTACHING TO</small><strong>{{ $task->title }}</strong><span>{{ $task->task_number ?: 'TASK-'.str_pad((string) $task->id, 5, '0', STR_PAD_LEFT) }} · {{ $task->phase?->name ?? 'Order Taskflow' }}</span><span><b>Order Reference:</b> {{ $job->order_number ?: '—' }}</span></div><em>Task selected</em></div>
            @endunless

            @if(!$prototypeUpload || !$canUpload)
                <div class="ft-order-task-document-source-tabs">
                    <button type="button" class="{{ $source === 'upload' ? 'active' : '' }}" wire:click="setOverviewTaskDocumentSource('upload')" @disabled(!$canUpload)>Upload new</button>
                    <button type="button" class="{{ $source === 'existing' ? 'active' : '' }}" wire:click="setOverviewTaskDocumentSource('existing')" @disabled(!$canLink)>Choose existing</button>
                </div>
            @endif

            @if($source === 'upload' && $canUpload)
                {{-- CHANGED 2026-08-24: local Livewire upload progress feedback. --}}
                <div
                    class="ft-order-popup-field"
                    x-data="{ uploading: false, progress: 0 }"
                    x-on:livewire-upload-start="uploading = true; progress = 0"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                    x-on:livewire-upload-finish="progress = 100; window.setTimeout(() => { uploading = false; progress = 0 }, 250)"
                    x-on:livewire-upload-error="uploading = false; progress = 0"
                    x-on:livewire-upload-cancel="uploading = false; progress = 0"
                >
                    @if($artworkRevisionActive)
                        <div class="ft-artwork-revision-upload-plan">
                            <div class="ft-artwork-revision-upload-plan-head">
                                <div>
                                    <strong>Select artwork that needs revision</strong>
                                    <span>Choose only the artwork file or files you are replacing now. Unselected artwork remains unchanged in the next version.</span>
                                </div>
                                <em>{{ $revisionCount }} selected</em>
                            </div>

                            <div class="ft-artwork-revision-upload-selector-list">
                                @foreach($allRevisionCandidates as $revisionCandidate)
                                    @php $candidateExtension = strtoupper(pathinfo((string) $revisionCandidate->name, PATHINFO_EXTENSION) ?: 'FILE'); @endphp
                                    <label class="ft-artwork-revision-upload-selector-item">
                                        <input type="checkbox" wire:model.live="overviewTaskRevisionDocumentIds" value="{{ $revisionCandidate->id }}">
                                        <span class="ft-artwork-revision-selector-check" aria-hidden="true">✓</span>
                                        <x-ui.file-type-badge :extension="$candidateExtension" size="sm" />
                                        <span class="ft-artwork-revision-upload-selector-copy">
                                            <b title="{{ $revisionCandidate->name }}">{{ $revisionCandidate->name }}</b>
                                            <small>{{ $candidateExtension }} · Version {{ max(1, (int) $revisionCandidate->version) }}</small>
                                        </span>
                                        <a href="{{ route('documents.open', $revisionCandidate) }}" target="_blank" rel="noopener" onclick="event.stopPropagation()">Open</a>
                                    </label>
                                @endforeach
                            </div>
                            @error('overviewTaskRevisionDocumentIds')<p class="validation-error">{{ $message }}</p>@enderror

                            @if(filled($artworkRevision['comment'] ?? null))
                                <div class="ft-artwork-revision-upload-instruction">
                                    <b>Revision instruction:</b>
                                    <div class="ft-rich-text-content"><x-ui.mention-text :text="$artworkRevision['comment']" /></div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($selectedUploads->isNotEmpty())
                        <div class="ft-order-attachment-selected-count">{{ $selectedUploadCount }} file{{ $selectedUploadCount === 1 ? '' : 's' }} selected{{ $artworkRevisionActive ? ' · '.$selectedUploadCount.' of '.$revisionCount.' replacements' : ($automationKey === 'ART_PREPARE_UPLOAD' ? ' · One artwork version' : '') }}</div>
                        @foreach($selectedUploadDetails as $index => $selectedUpload)
                            <div class="ft-order-attachment-selected-file" wire:key="overview-task-upload-{{ $task->id }}-{{ $index }}-{{ md5($selectedUpload['name']) }}">
                                @if($artworkRevisionActive)
                                    <x-ui.file-type-badge :extension="$selectedUpload['type']" size="sm" />
                                @else
                                    <span class="ft-order-attachment-selected-check" aria-hidden="true">✓</span>
                                @endif
                                <span class="ft-order-attachment-selected-copy">
                                    <strong class="{{ $prototypeUpload ? 'ft-prototype-selected-file-name' : '' }}" title="{{ $selectedUpload['name'] }}">{{ $selectedUpload['name'] }}</strong>
                                    <small>{{ $selectedUpload['type'] }} · {{ $selectedUpload['size'] }} · Ready to upload</small>
                                </span>
                                <button type="button" wire:click="removeOverviewTaskDocumentUpload({{ $index }})" wire:loading.attr="disabled" wire:target="overviewTaskDocumentUpload,removeOverviewTaskDocumentUpload({{ $index }})">Remove</button>
                            </div>
                        @endforeach
                    @else
                        <div class="{{ $prototypeUpload ? 'ft-prototype-upload-label' : 'ft-order-attachment-field-label' }}">{{ $prototypeUpload ? $prototypeConfig['label'] : 'File attachment' }}</div>
                    @endif

                    <label class="ft-order-task-document-dropzone ft-order-attachment-dropzone {{ $selectedUploads->isNotEmpty() ? 'is-compact' : '' }}">
                        <input type="file" wire:model="overviewTaskDocumentUpload" @if($inputAllowsMultiple) multiple @endif accept="{{ \App\Support\AttachmentUpload::accept() }}" aria-label="{{ $uploadCopyPlural ? 'Choose files to upload' : 'Choose a file to upload' }}" title="{{ $uploadCopyPlural ? 'Choose files' : 'Choose file' }}">
                        <svg class="ft-order-attachment-upload-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 16l-4-4-4 4M12 12v9M20.4 17.5A5 5 0 0 0 18 8.2 7 7 0 0 0 4.3 10.8 4.5 4.5 0 0 0 5.5 19H7"/></svg>
                        @if($selectedUploads->isNotEmpty())
                            <strong>{{ $uploadCopyPlural ? 'Choose a different file set' : 'Choose a different file' }}</strong>
                            <b>Drag &amp; drop or <span>browse</span></b>
                        @else
                            <strong>Drag &amp; drop {{ $uploadCopyPlural ? 'files' : 'a file' }} here</strong>
                            <b>or choose from your computer{{ $uploadCopyPlural ? ' (use Shift/Ctrl/Cmd to select several)' : '' }}</b>
                            <span class="ft-order-attachment-browse">Browse file{{ $uploadCopyPlural ? 's' : '' }}</span>
                        @endif
                        <small>{{ $prototypeConfig['hint'] }}</small>
                    </label>

                    {{-- CHANGED: shown only while Livewire is transferring the selected file. --}}
                    <div
                        class="ft-prototype-upload-progress"
                        x-cloak
                        x-show="uploading"
                        x-transition.opacity.duration.120ms
                    >
                        <div class="ft-prototype-upload-progress-meta">
                            <span>Uploading {{ $uploadCopyPlural ? 'files' : 'file' }}...</span>

                            <b x-text="`${progress}%`">
                                0%
                            </b>
                        </div>

                        <div
                            class="ft-prototype-upload-progress-track"
                            role="progressbar"
                            aria-label="File upload progress"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            x-bind:aria-valuenow="progress"
                        >
                            <span
                                x-bind:style="`width: ${progress}%`"
                            ></span>
                        </div>
                    </div>

                    @error('overviewTaskDocumentUpload')
                        <p class="validation-error">
                            {{ $message }}
                        </p>
                    @enderror
                    @error('overviewTaskDocumentUpload.*')
                        <p class="validation-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            @endif

            @unless($prototypeUpload)
                <label class="ft-order-task-document-note"><span>Document note (optional)</span><input type="text" wire:model="overviewTaskDocumentNote" placeholder="Add a short note...">@error('overviewTaskDocumentNote')<p class="validation-error">{{ $message }}</p>@enderror</label>
                <div class="ft-order-task-document-info">This document remains linked to this task and is available from the task and Documents archive.</div>
            @else
                <div class="ft-prototype-upload-meta"><span>Task</span><b>{{ $task->title }}</b><span>Order</span><b>{{ $job->displayOrderNumber() }}</b></div>
            @endunless
        </div>
        <footer class="ft-order-task-document-modal-actions">
            <button type="button" class="secondary" wire:click="closeOverviewTaskDocumentModal">Cancel</button>
            <button
                type="button"
                class="primary"
                wire:click="saveOverviewTaskDocument"
                wire:loading.attr="disabled"
                wire:target="saveOverviewTaskDocument,overviewTaskDocumentUpload"
                @disabled($source === 'upload' ? ($selectedUploads->isEmpty() || ($artworkRevisionActive && $revisionCount < 1)) : !$existingDocumentId)
            >
                <span
                    wire:loading.remove
                    wire:target="saveOverviewTaskDocument"
                >
                    {{
                        $prototypeUpload
                            ? $prototypeConfig['button']
                            : ($selectedUploadCount > 0 ? 'Add '.$selectedUploadCount.' document'.($selectedUploadCount === 1 ? '' : 's') : 'Add document')
                    }}
                </span>

                <span
                    wire:loading
                    wire:target="saveOverviewTaskDocument"
                >
                    Uploading...
                </span>
            </button>
        </footer>
    </section>
</div>
