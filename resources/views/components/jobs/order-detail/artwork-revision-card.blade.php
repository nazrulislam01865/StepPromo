@props([
    'revisionNote',
    'canExportDocument' => false,
])
@php
    $revisionComment = trim((string) data_get($revisionNote->meta, 'revision_comment', ''));
    if ($revisionComment === '') {
        $description = (string) $revisionNote->description;
        $revisionComment = str_contains($description, ': ')
            ? \Illuminate\Support\Str::after($description, ': ')
            : $description;
    }

    $referenceDocument = $revisionNote->relationLoaded('referenceDocument')
        ? $revisionNote->getRelation('referenceDocument')
        : null;
    $revisionDocuments = $revisionNote->relationLoaded('revisionDocuments')
        ? collect($revisionNote->getRelation('revisionDocuments'))->values()
        : ($referenceDocument ? collect([$referenceDocument]) : collect());
    $revisionAttachments = $revisionNote->relationLoaded('revisionAttachments')
        ? collect($revisionNote->getRelation('revisionAttachments'))->values()
        : collect();
    $richText = app(\App\Services\RichTextService::class);
    $requiredChangeText = $richText->withoutImages($revisionComment);
    $requiredChangeImages = collect($richText->imageAttachments($revisionComment));
@endphp

<section class="ft-order-artwork-revision-card" wire:key="order-task-artwork-revision-{{ $revisionNote->id }}">
    <div class="ft-order-artwork-revision-head">
        <span class="ft-order-artwork-revision-alert" aria-hidden="true">
            <span>!</span>
        </span>

        <div class="ft-order-artwork-revision-heading">
            <div class="ft-order-artwork-revision-kicker">Revision required</div>
            <div class="ft-order-artwork-revision-title">Artwork revision issue</div>
            <div class="ft-order-artwork-revision-meta">
                Requested by {{ $revisionNote->user?->name ?? 'FlowTrack' }} · {{ \App\Support\UserLocalTime::format($revisionNote->created_at, 'M j, Y, g:i A') }}
            </div>
        </div>

        <span class="ft-order-artwork-revision-badge">Revision</span>
    </div>

    <div class="ft-order-artwork-revision-grid">
        <div class="ft-order-artwork-revision-field ft-order-artwork-revision-change-field">
            <div class="ft-order-artwork-revision-label">Required change</div>
            @if(filled($requiredChangeText))
                <div class="ft-order-artwork-revision-change ft-rich-text-content"><x-ui.mention-text :text="$requiredChangeText" /></div>
            @endif

            @if($requiredChangeImages->isNotEmpty() || $revisionAttachments->isNotEmpty())
                <div class="ft-order-artwork-revision-reviewer-files">
                    <div class="ft-order-artwork-revision-label">Attachments from reviewer</div>
                    <div class="ft-order-artwork-revision-reviewer-file-list">
                        @foreach($requiredChangeImages as $image)
                            <div class="ft-order-artwork-revision-reviewer-file">
                                <x-ui.file-type-badge :extension="$image['extension']" size="sm" />
                                <span class="ft-order-artwork-revision-file-copy">
                                    <b>{{ $image['name'] }}</b>
                                    <small>{{ $image['extension'] }} · Reference</small>
                                </span>
                                <span class="ft-order-artwork-revision-file-actions">
                                    <a href="{{ $image['url'] }}" target="_blank" rel="noopener">Open</a>
                                    @if($canExportDocument)
                                        <span class="ft-order-artwork-revision-action-divider" aria-hidden="true"></span>
                                        <a href="{{ $image['download_url'] }}">Download</a>
                                    @endif
                                </span>
                            </div>
                        @endforeach

                        @foreach($revisionAttachments as $attachment)
                            @php $attachmentExtension = strtoupper(pathinfo((string) $attachment->name, PATHINFO_EXTENSION) ?: 'FILE'); @endphp
                            <div class="ft-order-artwork-revision-reviewer-file">
                                <x-ui.file-type-badge :extension="$attachmentExtension" size="sm" />
                                <span class="ft-order-artwork-revision-file-copy">
                                    <b>{{ $attachment->name }}</b>
                                    <small>{{ $attachmentExtension }} · Revision reference</small>
                                </span>
                                <span class="ft-order-artwork-revision-file-actions">
                                    <a href="{{ route('documents.open', $attachment) }}" target="_blank" rel="noopener">Open</a>
                                    @if($canExportDocument)
                                        <span class="ft-order-artwork-revision-action-divider" aria-hidden="true"></span>
                                        <a href="{{ route('documents.download', $attachment) }}">Download</a>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="ft-order-artwork-revision-field ft-order-artwork-revision-selected-field">
            <div class="ft-order-artwork-revision-label">Artwork selected for revision</div>

            @if($revisionDocuments->isNotEmpty())
                <div class="ft-order-artwork-revision-attachment-list">
                    @foreach($revisionDocuments as $revisionDocument)
                        @php $revisionExtension = strtoupper(pathinfo((string) $revisionDocument->name, PATHINFO_EXTENSION) ?: 'FILE'); @endphp
                        <div class="ft-order-artwork-revision-attachment">
                            <x-ui.file-type-badge :extension="$revisionExtension" class="ft-order-artwork-revision-file-icon" />
                            <span class="ft-order-artwork-revision-file-copy">
                                <b>{{ $revisionDocument->name }} · Version {{ max(1, (int) $revisionDocument->version) }}</b>
                                <small>{{ $revisionExtension }} · Only this selected artwork needs replacement</small>
                            </span>
                            <span class="ft-order-artwork-revision-file-actions">
                                <a href="{{ route('documents.open', $revisionDocument) }}" target="_blank" rel="noopener">Open</a>
                                @if($canExportDocument)
                                    <span class="ft-order-artwork-revision-action-divider" aria-hidden="true"></span>
                                    <a href="{{ route('documents.download', $revisionDocument) }}">Download</a>
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="ft-order-artwork-revision-attachment ft-order-artwork-revision-attachment--empty">
                    Choose the artwork to replace from the Upload Revised Artwork action.
                </div>
            @endif
        </div>
    </div>
</section>
