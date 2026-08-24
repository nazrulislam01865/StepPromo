            <section class="ft-detail-card ft-attachment-card">
                <h2>Attachments <span>{{ $task->documents->count() + ($task->relationLoaded('links') ? $task->links->count() : 0) }}</span></h2>
                <div class="ft-upload-zone compact ft-task-upload-zone">
                    @if($canUploadDocument && !$showTaskDocumentPicker)
                        <label class="ft-task-upload-drop ft-livewire-upload-zone" data-file-dropzone data-auto-upload-method="uploadSelectedTaskDocuments" for="taskDocumentUpload-{{ $task->id }}">
                            <input id="taskDocumentUpload-{{ $task->id }}" type="file" wire:model="taskDocumentUploads" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip,.txt,.csv,.eps,.esp">
                            <span class="ft-paperclip">⌕</span>
                            <div>Drop files here or <strong>browse</strong><small data-drop-status>{{ $taskDocumentName ? 'Required document: '.$taskDocumentName.' · ' : '' }}PDF, Office files, JPG, PNG, ZIP, EPS or ESP · Max 20 MB</small></div>
                            @if($taskDocumentInstructions !== '')<div class="ft-upload-note">{{ $taskDocumentInstructions }}</div>@endif
                        </label>
                    @elseif(!$canUploadDocument)
                        <div class="ft-task-upload-drop ft-task-upload-readonly"><span class="ft-paperclip">⌕</span><div>Attachments<small>You have read-only access to task attachments.</small></div></div>
                    @endif
                    @if($canLinkDocument)<button class="ft-outline-btn ft-task-choose-document" type="button" wire:click="toggleTaskDocumentPicker">{{ $showTaskDocumentPicker && $canUploadDocument ? 'Upload new' : 'Choose from Documents' }}</button>@endif
                </div>
                @if(!$showTaskDocumentPicker && count($taskDocumentUploads ?? []))
                    <div class="ft-upload-ready-row ft-auto-upload-state" aria-live="polite"><span>Uploading and linking {{ count($taskDocumentUploads ?? []) }} file{{ count($taskDocumentUploads ?? [])===1?'':'s' }} automatically…</span></div>
                @endif
                @error('taskDocumentUploads')<div class="validation-error">{{ $message }}</div>@enderror
                @error('taskDocumentUploads.*')<div class="validation-error">{{ $message }}</div>@enderror
                @if($canLinkDocument && $showTaskDocumentPicker)
                    <div class="ft-existing-document-picker ft-task-document-picker">
                        <select wire:model="taskExistingDocumentId"><option value="">Select a stored document</option>@foreach($availableDocuments as $stored)<option value="{{ $stored->id }}">{{ $stored->name }} · {{ $stored->job?->displayOrderNumber() ?? 'Archive' }}</option>@endforeach</select>
                        <button class="ft-new-job-btn" type="button" wire:click="attachExistingToSelectedTask">Link document</button>
                        <button class="ft-outline-btn" type="button" wire:click="toggleTaskDocumentPicker">Cancel</button>
                    </div>
                    @error('taskExistingDocumentId')<div class="validation-error">{{ $message }}</div>@enderror
                @endif
                @if($task->documents->isNotEmpty())
                    <div class="ft-task-attachment-list" aria-label="Task attachments">
                        @foreach($task->documents->sortByDesc('created_at') as $doc)
                            <div class="ft-order-task-document-row ft-task-detail-document-row" wire:key="task-detail-document-{{ $doc->id }}">
                                <span class="ft-order-task-file-type">{{ strtoupper(pathinfo($doc->name, PATHINFO_EXTENSION) ?: 'FILE') }}</span>
                                <div class="ft-order-task-file-copy">
                                    <b title="{{ $doc->name }}">{{ $doc->name }}</b>
                                    @if($doc->note)<span class="ft-order-task-file-note">{{ $doc->note }}</span>@endif
                                    <small>{{ $doc->category ?: 'Task attachment' }} · {{ $doc->uploader?->name ?? 'FlowTrack' }} · {{ \App\Support\UserLocalTime::format($doc->created_at, 'M j, Y, g:i A') }}</small>
                                </div>
                                <div class="ft-order-task-file-actions">
                                    <a href="{{ route('documents.open', $doc) }}" target="_blank" rel="noopener">Open</a>
                                    @if(auth()->user()->canModule('documents','export'))<a href="{{ route('documents.download', $doc) }}">Download</a>@endif
                                    @if($canDeleteDocument)
                                        <button type="button" wire:click="deleteSelectedTaskDocument({{ $doc->id }})" wire:loading.attr="disabled" wire:target="deleteSelectedTaskDocument({{ $doc->id }})" wire:confirm="Delete this document link?" title="Remove attachment" aria-label="Remove {{ $doc->name }}">×</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                @if($task->relationLoaded('links') && $task->links->isNotEmpty())
                    <div class="ft-task-attachment-list ft-task-external-link-list" aria-label="Task external links">
                        @foreach($task->links as $taskLink)
                            <div class="ft-order-task-link-row" wire:key="task-detail-link-{{ $taskLink->id }}">
                                <span class="ft-order-task-link-type" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                </span>
                                <div class="ft-order-task-link-copy">
                                    <a href="{{ $taskLink->url }}" target="_blank" rel="noopener noreferrer" title="{{ $taskLink->url }}">{{ \Illuminate\Support\Str::limit($taskLink->url, 110) }}</a>
                                    <small>External link · {{ $taskLink->created_at ? \App\Support\UserLocalTime::format($taskLink->created_at, 'M j, Y, g:i A') : '—' }}</small>
                                </div>
                                <div class="ft-order-task-link-actions">
                                    <a href="{{ $taskLink->url }}" target="_blank" rel="noopener noreferrer">Open ↗</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <p class="ft-upload-note">Files and external links attached to this task remain available here and in the Order taskflow. Either can satisfy a Task Pack document requirement.</p>
            </section>
