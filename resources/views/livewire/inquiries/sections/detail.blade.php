        @php
            $inquiry = $selectedInquiry;
            $totalTasks = (int) $inquiry->tasks_count;
            $completedTasks = (int) $inquiry->completed_tasks_count;
            $readyForDecision = !$inquiry->result && $totalTasks > 0 && $completedTasks === $totalTasks;
            $currentTask = $inquiry->currentTask;
            $firstStartedTask = $inquiry->tasks->whereNotNull('started_at')->sortBy('started_at')->first();
            $lastCompletedTask = $inquiry->tasks->whereNotNull('completed_at')->sortByDesc('completed_at')->first();
            $inquiryStartAt = $inquiry->started_at ?: $firstStartedTask?->started_at;
            $inquiryStartLocal = \App\Support\UserLocalTime::localize($inquiryStartAt);
            $inquiryCompletedAt = $inquiry->completed_at ?: ($readyForDecision ? $lastCompletedTask?->completed_at : null);
            $detailStatus = match (true) {
                $inquiry->result === 'converted' => 'Converted',
                $inquiry->result === 'dead' => 'Closed',
                (string) $inquiry->status === 'Draft' => 'Draft',
                default => (string) ($inquiry->status ?: $inquiryDefaultStatus),
            };
            $detailStatusColor = $detailStatusColor ?? null;
            $detailPriorityColor = $masterData->displayColorFor('priority', (string) $inquiry->priority);
            $headerFlagTask = $currentTask?->needs_attention ? $currentTask : $inquiry->tasks->first(fn ($task) => (bool) $task->needs_attention);
            $headerFlagLabel = $inquiry->needs_attention
                ? 'Requires attention'
                : ($headerFlagTask ? 'Requires attention' : '');
            $headerFlagReason = $inquiry->needs_attention
                ? (string) ($inquiry->attention_reason ?? '')
                : (string) ($headerFlagTask?->attention_reason ?? '');
        @endphp
        <section class="view inquiry-detail-view ft-detail-products-scope" x-data="{
            inquiryStatus:@js($detailStatus),
            inquiryStatusColor:@js($detailStatusColor),
            inquiryStartValue:@js($inquiryStartLocal?->format('Y-m-d\TH:i') ?? ''),
            inquiryStartDisplay:@js($inquiryStartLocal?->format('M j, Y · g:i A') ?? '—'),
            statusTone(status){
                if (String(status).includes('Converted') || String(status).includes('Completed')) return 'green';
                if (String(status).includes('Dead') || String(status).includes('Closed')) return 'red';
                if (String(status).includes('Ready') || String(status).includes('On Hold')) return 'amber';
                if (String(status).includes('Waiting')) return 'purple';
                return 'blue';
            },
            async saveTaskStatus(event, taskId){
                const previous=this.inquiryStatus;
                try{
                    const result=await $wire.updateTaskStatusInline(taskId,event.currentTarget.value);
                    if(result?.inquiryStatus)this.inquiryStatus=result.inquiryStatus;
                    if(result?.inquiryColor)this.inquiryStatusColor=result.inquiryColor;
                    if(result && Object.prototype.hasOwnProperty.call(result,'inquiryStartValue')){
                        this.inquiryStartValue=result.inquiryStartValue || '';
                        this.inquiryStartDisplay=result.inquiryStartDisplay || '—';
                        window.dispatchEvent(new CustomEvent('flowtrack-inquiry-started',{detail:{value:this.inquiryStartValue,display:this.inquiryStartDisplay}}));
                    }
                }catch(error){
                    this.inquiryStatus=previous;
                    window.location.reload();
                }
            }
        }">
            <div class="ft-detail-toolbar task-toolbar ft-exact-task-header ft-inquiry-exact-header">
                <div class="ft-task-heading-copy">
                    <div class="ft-detail-breadcrumb ft-id-breadcrumb">
                        <a href="{{ route('inquiries.index') }}" wire:navigate>Inquiries</a>
                        <span>/</span>
                        <span class="ft-copyable-id-wrap ft-inquiry-detail-code-wrap">
                            <span>{{ $inquiry->inquiry_number }}</span>
                            <button type="button" class="ft-copy-id-btn" title="Copy Inquiry ID" aria-label="Copy {{ $inquiry->inquiry_number }}" onclick="event.preventDefault(); event.stopPropagation(); navigator.clipboard?.writeText(@js($inquiry->inquiry_number)); this.classList.add('copied'); setTimeout(()=>this.classList.remove('copied'),900)">⧉</button>
                        </span>
                    </div>
                    <div class="ft-task-title-line">
                        <h1
                            class="ft-editable-task-title ft-inline-edit-shell"
                            x-data="window.FlowTrack.ui.inlineEdit({ key: @js('inquiry-'.$inquiry->id.'-title'), label: 'Inquiry title', value: @js($inquiry->subject), display: @js($inquiry->subject) })"
                            :class="{ 'is-inline-saving': status === 'saving', 'is-inline-error': status === 'error' }"
                        >
                            <span x-show="!editing" x-text="display">{{ $inquiry->subject }}</span>
                            @if($canEditInquiry && !$inquiry->result)
                                <button x-show="!editing" :disabled="status === 'saving'" type="button" class="ft-pencil" aria-label="Edit Inquiry title" title="Edit Inquiry title" x-on:click.stop="if (beginEdit()) $nextTick(() => $refs.inquiryTitle.focus())">✎</button>
                                <input x-ref="inquiryTitle" x-cloak x-show="editing" x-model="draftValue" type="text" maxlength="255"
                                    x-on:keydown.escape.prevent="cancelEdit()"
                                    x-on:keydown.enter.prevent="$event.target.blur()"
                                    x-on:blur="if (editing) commit(draftValue.trim(), draftValue.trim(), () => $wire.updateInquiryField('subject', draftValue.trim()))">
                                <x-ui.inline-save-state />
                            @endif
                        </h1>
                    </div>
                    <div class="ft-inquiry-header-meta" aria-label="Inquiry information">
                        <span class="ft-inquiry-header-meta-item"><span class="ft-inquiry-header-meta-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.5"></circle><path d="M5.5 19c.8-3.4 3-5.2 6.5-5.2s5.7 1.8 6.5 5.2"></path></svg></span><span class="ft-client-inline-identity"><x-ui.client-logo :client="$inquiry->client" :name="$inquiry->client?->name ?: 'Client'" :size="20" /><span>Client <strong>{{ $inquiry->client?->name ?: '—' }}</strong></span></span></span>
                        <span class="ft-inquiry-header-meta-separator" aria-hidden="true">•</span>
                        <span class="ft-inquiry-header-meta-item"><span class="ft-inquiry-header-meta-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.5"></circle><path d="M5.5 19c.8-3.4 3-5.2 6.5-5.2s5.7 1.8 6.5 5.2"></path></svg></span><span>Client contact <strong>{{ $inquiry->client_contact ?: '—' }}</strong></span></span>
                        <span class="ft-inquiry-header-meta-separator" aria-hidden="true">•</span>
                        <span class="ft-inquiry-header-meta-item ft-inquiry-header-reference">
                            <span class="ft-inquiry-header-meta-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M7 3.5h7l4 4V20.5H7z"></path><path d="M14 3.5v4h4"></path></svg></span>
                            <span>Reference <strong>{{ $inquiry->reference_number ?: '—' }}</strong></span>
                            @if($inquiry->reference_number)
                                <button type="button" class="ft-copy-id-btn ft-inquiry-header-copy" title="Copy Reference Number" aria-label="Copy reference number {{ $inquiry->reference_number }}" onclick="event.preventDefault(); event.stopPropagation(); navigator.clipboard?.writeText(@js($inquiry->reference_number)); this.classList.add('copied'); setTimeout(()=>this.classList.remove('copied'),900)">⧉</button>
                            @endif
                        </span>
                        <span class="ft-inquiry-header-meta-separator" aria-hidden="true">•</span>
                        <span class="ft-inquiry-header-meta-item"><span class="ft-inquiry-header-meta-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.5"></circle><path d="M5.5 19c.8-3.4 3-5.2 6.5-5.2s5.7 1.8 6.5 5.2"></path></svg></span><span>Created by <strong>{{ $inquiry->creator?->name ?: 'System' }}</strong></span></span>
                        <span class="ft-inquiry-header-meta-separator" aria-hidden="true">•</span>
                        <span class="ft-inquiry-header-meta-item"><span class="ft-inquiry-header-meta-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="4" y="5.5" width="16" height="14" rx="2"></rect><path d="M8 3.5v4M16 3.5v4M4 10h16"></path></svg></span><span>Created <strong>{{ $inquiry->created_at ? \App\Support\UserLocalTime::format($inquiry->created_at, 'M j, Y') : '—' }}@if($inquiry->created_at) at {{ \App\Support\UserLocalTime::format($inquiry->created_at, 'g:i A') }}@endif</strong></span></span>
                        <span class="ft-inquiry-header-meta-separator" aria-hidden="true">•</span>
                        <span class="ft-inquiry-header-meta-item ft-inquiry-header-action" title="{{ $headerFlagReason ?: 'Request attention from the Inquiry creator and administrators' }}">
                            <span>Action:</span>
                            <button type="button" class="ft-inquiry-header-flag-button {{ $headerFlagLabel !== '' ? 'is-flagged' : '' }}" wire:click="openInquiryAttentionReason" @disabled($inquiry->result) aria-label="Request attention" title="{{ $headerFlagLabel !== '' ? 'View or update attention request' : 'Request attention' }}">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 21V4"></path><path d="M7 5h10l-2 4 2 4H7"></path></svg>
                            </button>
                            @if($headerFlagLabel !== '')<strong class="ft-inquiry-header-flag-label">{{ $headerFlagLabel }}</strong>@endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="tabs">
                <button class="tab active" type="button">Overview</button>
            </div>

            @if($detailTab === 'overview')
                <div class="tabpane ft-task-detail-page ft-exact-task-detail ft-inquiry-task-overview-exact">
                    <section class="ft-task-property-grid ft-friendly-task-properties ft-inquiry-overview-properties">
                        <div class="ft-task-property ft-inquiry-auto-status-property">
                            <small>Status</small>
                            <div class="ft-task-property-display">
                                <span class="status-dot {{ $detailStatusColor ? 'ft-master-color-dot' : '' }}" style="{{ \App\Support\MasterColor::style($detailStatusColor) }}" x-bind:class="inquiryStatusColor ? 'ft-master-color-dot' : statusTone(inquiryStatus)" x-bind:style="inquiryStatusColor ? '--ft-master-color:'+inquiryStatusColor : ''"></span>
                                <b class="ft-property-value" x-text="inquiryStatus">{{ $detailStatus }}</b>
                            </div>
                        </div>

                        <div
                            class="ft-task-property ft-inline-edit-shell"
                            x-data="{ ...window.FlowTrack.ui.inlineEdit({ key: @js('inquiry-'.$inquiry->id.'-priority'), label: 'Inquiry priority', value: @js($inquiry->priority), display: @js($inquiry->priority) }), priorityColor: @js($detailPriorityColor) }"
                            :class="{ 'is-inline-saving': status === 'saving', 'is-inline-error': status === 'error' }"
                            x-on:click.outside="if (editing) cancelEdit()"
                        >
                            <small>Priority</small>
                            <div x-show="!editing" class="ft-task-property-display"><span class="status-dot ft-master-color-dot" style="{{ \App\Support\MasterColor::style($detailPriorityColor) }}" x-bind:style="priorityColor ? '--ft-master-color:'+priorityColor : ''"></span><b class="ft-property-value" x-text="display">{{ $inquiry->priority }}</b>@if($canEditInquiry && !$inquiry->result)<button type="button" :disabled="status === 'saving'" title="Edit priority" x-on:click.stop="if (beginEdit()) $nextTick(() => $refs.inquiryPriority?.showPicker ? $refs.inquiryPriority.showPicker() : $refs.inquiryPriority?.focus())">✎</button>@endif</div>
                            @if($canEditInquiry && !$inquiry->result)
                                <div x-cloak x-show="editing" class="ft-task-property-inline-editor"><select data-master-color-select x-ref="inquiryPriority" x-model="draftValue" class="ft-task-property-inline-input ft-master-color" style="{{ \App\Support\MasterColor::style($detailPriorityColor) }}" x-on:keydown.escape.prevent="cancelEdit()" x-on:change="const nextColor=String($event.target.selectedOptions[0]?.dataset?.color || ''); window.FlowTrack.ui.masterColor?.applySelect($event.target); commit($event.target.value, selectedLabel($event), () => $wire.updateInquiryField('priority', draftValue)).then(ok => { if(ok) priorityColor=nextColor; });">@unless($inquiryPriorities->contains(fn($priority) => (string) $priority->name === (string) $inquiry->priority))<option value="{{ $inquiry->priority }}" data-color="{{ $masterData->displayColorFor('priority', (string) $inquiry->priority) }}">{{ $inquiry->priority }}</option>@endunless @foreach($inquiryPriorities as $priority)<option value="{{ $priority->name }}" data-color="{{ $masterData->displayColorFor('priority', $priority->name) }}">{{ $priority->name }}</option>@endforeach</select></div>
                                <x-ui.inline-save-state compact />
                            @endif
                        </div>

                        <div
                            class="ft-task-property ft-inline-edit-shell"
                            x-data="window.FlowTrack.ui.inlineEdit({ key: @js('inquiry-'.$inquiry->id.'-assignee'), label: 'Inquiry assignee', value: @js($inquiry->owner_id ?? ''), display: @js($inquiry->owner?->name ?? 'Unassigned'), avatarUrl: @js($inquiry->owner?->profileImageUrl() ?? '') })"
                            :class="{ 'is-inline-saving': status === 'saving', 'is-inline-error': status === 'error' }"
                            x-on:click.outside="if (editing) cancelEdit()"
                            x-on:ft-inline-remote-cancel.stop="cancelEdit()"
                            x-on:ft-inline-remote-selected.stop="commit(String($event.detail?.value ?? ''), String($event.detail?.label ?? 'Unassigned'), () => $wire.updateInquiryField('owner_id', draftValue), { avatarUrl: String($event.detail?.avatarUrl ?? '') })"
                        >
                            <small>Assignee</small>
                            <div x-show="!editing" class="ft-task-property-display ft-inline-person-live">
                                <x-ui.inline-live-avatar :size="26" />
                                <b class="ft-property-value" x-text="display">{{ $inquiry->owner?->name ?? 'Unassigned' }}</b>
                                @if($canEditInquiry && $canAssignInquiry && !$inquiry->result)<button type="button" :disabled="status === 'saving'" title="Edit assignee" aria-label="Edit Inquiry assignee" x-on:click.stop="openRemotePicker($event.currentTarget)">✎</button>@endif
                            </div>
                            @if($canEditInquiry && $canAssignInquiry && !$inquiry->result)
                                <div x-cloak x-show="editing" class="ft-task-property-inline-editor ft-task-property-assignee-editor">
                                    <x-ui.inline-remote-user
                                        :value="$inquiry->owner_id ?? ''"
                                        :selected-label="$inquiry->owner?->name ?? 'Unassigned'"
                                        context="inquiry-owner"
                                        parent-type="inquiry"
                                        :parent-id="$inquiry->id"
                                        search-placeholder="Search assignee…"
                                        trigger-class="ft-task-property-inline-input"
                                        variant="compact"
                                        :menu-width="300"
                            :fixed-menu="true"
                                    />
                                </div>
                                <x-ui.inline-save-state compact />
                            @endif
                        </div>

                        <div
                            class="ft-task-property ft-inline-edit-shell"
                            x-data="window.FlowTrack.ui.inlineEdit({ key: @js('inquiry-'.$inquiry->id.'-due-date'), label: 'Inquiry next due date', value: @js($currentTask?->due_date?->format('Y-m-d') ?? ''), display: @js($currentTask?->due_date?->format('M j, Y') ?? '—') })"
                            :class="{ 'is-inline-saving': status === 'saving', 'is-inline-error': status === 'error' }"
                            x-on:click.outside="if (editing) cancelEdit()"
                        >
                            <small>Due date</small>
                            <div x-show="!editing" class="ft-task-property-display"><span class="ft-calendar-glyph">▣</span><b class="ft-property-value" x-text="display">{{ $currentTask?->due_date?->format('M j, Y') ?? '—' }}</b>@if($currentTask && $canEditActiveTask)<button type="button" :disabled="status === 'saving'" title="Edit due date" x-on:click.stop="if (beginEdit()) $nextTick(() => $refs.inquiryOverviewDue?.showPicker ? $refs.inquiryOverviewDue.showPicker() : $refs.inquiryOverviewDue?.focus())">✎</button>@endif</div>
                            @if($currentTask && $canEditActiveTask)
                                <div x-cloak x-show="editing" class="ft-task-property-inline-editor"><input x-ref="inquiryOverviewDue" x-model="draftValue" class="ft-task-property-inline-input" type="date" x-on:keydown.escape.prevent="cancelEdit()" x-on:change="commit($event.target.value, formatDate($event.target.value), () => $wire.updateTaskDueInline({{ $currentTask->id }}, draftValue))"></div>
                                <x-ui.inline-save-state compact />
                            @endif
                        </div>

                        <div
                            class="ft-task-property ft-inline-edit-shell"
                            x-data="window.FlowTrack.ui.inlineEdit({ key: @js('inquiry-'.$inquiry->id.'-start-at'), label: 'Inquiry start date', value: @js($inquiryStartLocal?->format('Y-m-d\TH:i') ?? ''), display: @js($inquiryStartLocal?->format('M j, Y · g:i A') ?? '—') })"
                            :class="{ 'is-inline-saving': status === 'saving', 'is-inline-error': status === 'error' }"
                            x-on:click.outside="if (editing) cancelEdit()"
                            x-on:flowtrack-inquiry-started.window="const v=String($event.detail?.value ?? ''); const d=String($event.detail?.display ?? '—'); serverValue=v; value=v; savedValue=v; draftValue=v; display=d; savedDisplay=d;"
                        >
                            <small>Start date</small>
                            <div x-show="!editing" class="ft-task-property-display">
                                <span class="ft-calendar-glyph">▣</span>
                                <b class="ft-property-value" x-text="display">{{ $inquiryStartLocal?->format('M j, Y · g:i A') ?? '—' }}</b>
                                @if($canEditInquiry && !$inquiry->result)
                                    <button type="button" :disabled="status === 'saving'" title="Edit start date and time" aria-label="Edit Inquiry start date and time" x-on:click.stop="if (beginEdit()) $nextTick(() => $refs.inquiryStartAt?.showPicker ? $refs.inquiryStartAt.showPicker() : $refs.inquiryStartAt?.focus())">✎</button>
                                @endif
                            </div>
                            @if($canEditInquiry && !$inquiry->result)
                                <div x-cloak x-show="editing" class="ft-task-property-inline-editor">
                                    <input x-ref="inquiryStartAt" x-model="draftValue" class="ft-task-property-inline-input" type="datetime-local" step="60"
                                        x-on:keydown.escape.prevent="cancelEdit()"
                                        x-on:change="commit($event.target.value, formatDateTime($event.target.value), () => $wire.updateInquiryStartInline(draftValue))">
                                </div>
                                <x-ui.inline-save-state compact />
                            @endif
                        </div>

                        <div class="ft-task-property ft-task-completed-property">
                            <small>Completed On</small>
                            <div class="ft-task-property-display"><span class="ft-calendar-glyph">▣</span><b class="ft-property-value ft-completed-date-time"><span>{{ $inquiryCompletedAt ? \App\Support\UserLocalTime::format($inquiryCompletedAt, 'M j, Y') : '—' }}</span>@if($inquiryCompletedAt)<span class="ft-completed-time">{{ \App\Support\UserLocalTime::format($inquiryCompletedAt, 'g:i A') }}</span>@endif</b></div>
                        </div>
                    </section>

                    <section
                        class="ft-detail-card ft-inquiry-description-card ft-inline-edit-shell"
                        x-data="window.FlowTrack.ui.inlineEdit({ key: @js('inquiry-'.$inquiry->id.'-description'), label: 'Inquiry description', value: @js($inquiry->requirement_notes ?? ''), display: @js($inquiry->requirement_notes ?: 'No description has been provided for this Inquiry.') })"
                        :class="{ 'is-inline-saving': status === 'saving', 'is-inline-error': status === 'error' }"
                    >
                        <div class="ft-inquiry-description-head">
                            <h2>Description</h2>
                            @if($canEditInquiry && !$inquiry->result)
                                <button x-show="!editing" :disabled="status === 'saving'" type="button" class="ft-inline-edit-button" title="Edit description" aria-label="Edit Inquiry description" x-on:click.stop="beginRichTextEdit($refs.inquiryDescription)">✎</button>
                            @endif
                        </div>
                        <div x-show="!editing" class="ft-rich-text-content ft-inquiry-description-content">
                            <div x-show="!hasRichTextOverride">@if($inquiry->requirement_notes)<x-ui.mention-text :text="$inquiry->requirement_notes" />@else No description has been provided for this Inquiry. @endif</div>
                            <div x-cloak x-show="hasRichTextOverride" x-html="richTextOverrideHtml"></div>
                        </div>
                        @if($canEditInquiry && !$inquiry->result)
                            <div x-cloak x-show="editing" class="ft-inquiry-description-editor ft-inline-description-editor">
                                <textarea x-ref="inquiryDescription" data-rich-text placeholder="Add the client requirement or Inquiry description, or paste screenshots here...">{{ $inquiry->requirement_notes ?? '' }}</textarea>
                                <div class="ft-inquiry-description-editor-actions">
                                    <button type="button" class="secondary" x-on:click="cancelRichTextEdit($refs.inquiryDescription)">Cancel</button>
                                    <button type="button" class="primary" data-rich-text-submit :disabled="status === 'saving'" x-on:click="saveRichText($refs.inquiryDescription, 'No description has been provided for this Inquiry.', (clean) => $wire.updateInquiryField('requirement_notes', clean))">Save</button>
                                    <x-ui.inline-save-state compact />
                                </div>
                            </div>
                        @endif
                    </section>

                    @if($canViewInquiryProducts)
                    @php
                        // Only persisted products belong in the details table. The shared
                        // Add Product panel owns the temporary selection state, exactly as
                        // it does on Order Details, so unfinished legacy draft rows stay out.
                        $inquiryItemRows = collect($inquiry->items ?? collect())
                            ->filter(fn ($item) => filled($item->item_name))
                            ->values();
                        $inquiryItemCount = $inquiryItemRows->count();
                        $inquiryItemUnits = (float) $inquiryItemRows->sum('quantity');
                    @endphp
                    <x-catalog.detail-products-card
                        id="inquiry-products-card"
                        variant="inquiry"
                        :count="$inquiryItemCount"
                        :total-units="$inquiryItemUnits"
                    >
                        @if($inquiryItemRows->isEmpty())
                            <tr class="ft-order-product-empty-row"><td colspan="7">No products have been added to this Inquiry yet.</td></tr>
                        @else
                            @foreach($inquiryItemRows as $item)
                                @php
                                    $isDraftInquiryItem = blank($item->item_name);
                                    $categoryNeedsSelection = filled($item->id) && blank($item->category);
                                    $productNeedsSelection = filled($item->id) && filled($item->category) && blank($item->item_name);
                                    $categoryLabel = $item->category ?: 'Select category';
                                    $productLabel = $item->item_name ?: (blank($item->category) ? 'Select category first' : 'Select product');
                                    $productPickerKey = 'inquiry-item-'.$item->id.'-product-'.md5((string) ($item->category ?? '').'|'.(string) ($item->item_name ?? ''));
                                    $productMaster = $inquiryProductMasters->get(mb_strtolower(trim((string) ($item->item_name ?? ''))));
                                    $productImageUrl = $productMaster?->productImageUrl();
                                    $productCode = $productMaster?->productDisplayCode();
                                    $productReference = $productMaster?->productReferenceCode();
                                    $classificationParts = collect([
                                        $productMaster?->productMainCategory(),
                                        ...array_filter(array_map('trim', preg_split('/\s*>\s*/', (string) ($productMaster?->productClassificationPath() ?? '')) ?: [])),
                                    ])->filter()->unique()->values();
                                    if ($classificationParts->isEmpty() && filled($item->category)) $classificationParts = collect([$item->category]);
                                    $categoryDisplay = $classificationParts->implode(' › ') ?: $categoryLabel;
                                    $unitPrice = $item->unit_price !== null ? (float) $item->unit_price : null;
                                    $unitPriceValue = $unitPrice !== null ? number_format($unitPrice, 2, '.', '') : '';
                                    $unitPriceDisplay = $unitPrice !== null ? $inquiryCurrencySymbol.number_format($unitPrice, 2) : '—';
                                    $updatedDate = $item->updated_at ? \App\Support\UserLocalTime::format($item->updated_at, 'M j, Y') : '—';
                                    $updatedTime = $item->updated_at ? \App\Support\UserLocalTime::format($item->updated_at, 'g:i A') : null;
                                @endphp
                                <tr
                                    wire:key="inquiry-product-detail-{{ $item->id }}"
                                    x-data="{ categorySaving: false, productSaving: false, quantitySaving: false, priceSaving: false, notesSaving: false, actionOpen: false, draftProductReady: @js(filled($item->item_name)) }"
                                    @class(['ft-order-product-draft-row' => $isDraftInquiryItem])
                                >
                                    <td data-label="Product">
                                        <x-catalog.detail-product-identity
                                            :image-url="$productImageUrl"
                                            :alt="$item->item_name ?? ''"
                                            :code="$productCode"
                                            :reference="$productReference"
                                            fallback-meta="Inquiry product"
                                        >
                                            <div
                                                class="ft-inline-field-editor ft-inline-edit-shell ft-inline-catalog-editor ft-order-product-name-editor"
                                                wire:key="{{ $productPickerKey }}"
                                                x-data="window.FlowTrack.ui.inlineEdit({ key: @js('inquiry-item-'.$item->id.'-product'), label: 'product', value: @js($item->item_name ?? ''), display: @js($productLabel) })"
                                                x-init="if (@js($canEditInquiryProducts && $productNeedsSelection)) { editing = true; $nextTick(() => setTimeout(() => { const picker = $el.querySelector('[data-ft-inline-remote-picker]'); picker?.dispatchEvent(new CustomEvent('ft-inline-remote-open', { detail: { value: value, label: display } })) }, 0)) }"
                                                :class="{ 'is-inline-saving': status === 'saving', 'is-inline-error': status === 'error' }"
                                                x-on:click.outside="if (editing && !@js($productNeedsSelection)) cancelEdit()"
                                                x-on:ft-inline-remote-cancel.stop="if (!@js($productNeedsSelection)) cancelEdit()"
                                                x-on:ft-inline-remote-selected.stop="const nextValue = String($event.detail?.value ?? ''); const nextLabel = String($event.detail?.label ?? 'Select product'); productSaving = true; commit(nextValue, nextLabel, () => $wire.updateInquiryItem({{ $item->id }}, 'item_name', nextValue)).then(async (ok) => { productSaving = false; if (ok) { draftProductReady = true; await $wire.$refresh(); } })"
                                            >
                                                <span class="ft-order-product-name" x-show="!editing" x-text="display">{{ $productLabel }}</span>
                                                @if($canEditInquiryProducts)
                                                    <button x-show="!editing" :disabled="status === 'saving' || categorySaving || quantitySaving || priceSaving || notesSaving || @js(blank($item->category))" type="button" class="ft-inline-edit-button" aria-label="Edit product" title="{{ blank($item->category) ? 'Select a category first' : 'Edit product' }}" x-on:click.stop="openRemotePicker($event.currentTarget)">✎</button>
                                                    <div x-cloak x-show="editing" class="ft-inline-catalog-picker">
                                                        <x-ui.inline-remote-catalog
                                                            type="products"
                                                            context="inquiry-detail"
                                                            :value="$item->item_name ?? ''"
                                                            :selected-label="$productLabel"
                                                            :placeholder="blank($item->category) ? 'Select category first' : 'Select product'"
                                                            search-label="product"
                                                            :params="['category' => (string) ($item->category ?? '')]"
                                                            :disabled="blank($item->category)"
                                                            :menu-width="360"
                                                            :fixed-menu="true"
                                                        />
                                                    </div>
                                                    <x-ui.inline-save-state compact />
                                                @endif
                                            </div>
                                        </x-catalog.detail-product-identity>
                                    </td>
                                    <td data-label="Category">
                                        <div
                                            class="ft-inline-field-editor ft-inline-edit-shell ft-inline-catalog-editor ft-order-product-category-editor"
                                            wire:key="inquiry-item-{{ $item->id }}-category-{{ md5((string) ($item->category ?? '')) }}"
                                            x-data="window.FlowTrack.ui.inlineEdit({ key: @js('inquiry-item-'.$item->id.'-category'), label: 'product category', value: @js($item->category ?? ''), display: @js($categoryDisplay) })"
                                            x-init="if (@js($canEditInquiryProducts && $categoryNeedsSelection)) { editing = true; $nextTick(() => setTimeout(() => { const picker = $el.querySelector('[data-ft-inline-remote-picker]'); picker?.dispatchEvent(new CustomEvent('ft-inline-remote-open', { detail: { value: value, label: display } })) }, 0)) }"
                                            :class="{ 'is-inline-saving': status === 'saving', 'is-inline-error': status === 'error' }"
                                            x-on:click.outside="if (editing && !@js($categoryNeedsSelection)) cancelEdit()"
                                            x-on:ft-inline-remote-cancel.stop="if (!@js($categoryNeedsSelection)) cancelEdit()"
                                            x-on:ft-inline-remote-selected.stop="const nextValue = String($event.detail?.value ?? ''); const nextLabel = String($event.detail?.label ?? 'Select category'); const changed = nextValue !== savedValue; categorySaving = true; commit(nextValue, nextLabel, () => $wire.updateInquiryItem({{ $item->id }}, 'category', nextValue)).then(async (ok) => { if (ok && changed) await $wire.$refresh(); categorySaving = false })"
                                        >
                                            <span class="ft-order-product-category-path" x-show="!editing" x-text="display">{{ $categoryDisplay }}</span>
                                            @if($canEditInquiryProducts)
                                                <button x-show="!editing" :disabled="status === 'saving' || productSaving || quantitySaving || priceSaving || notesSaving" type="button" class="ft-inline-edit-button" aria-label="Edit product category" title="Edit category" x-on:click.stop="openRemotePicker($event.currentTarget)">✎</button>
                                                <div x-cloak x-show="editing" class="ft-inline-catalog-picker">
                                                    <x-ui.inline-remote-catalog
                                                        type="product-categories"
                                                        context="inquiry-detail"
                                                        :value="$item->category ?? ''"
                                                        :selected-label="$categoryLabel"
                                                        placeholder="Select category"
                                                        search-label="product category"
                                                        :menu-width="340"
                                                        :fixed-menu="true"
                                                    />
                                                </div>
                                                <x-ui.inline-save-state compact />
                                            @endif
                                        </div>
                                    </td>
                                    <td class="ft-order-product-quantity" data-label="Quantity">
                                        <div
                                            class="ft-inline-field-editor ft-inline-edit-shell"
                                            x-data="window.FlowTrack.ui.inlineEdit({ key: @js('inquiry-item-'.$item->id.'-quantity'), label: 'quantity', value: @js((string) max(1, (int) $item->quantity)), display: @js(number_format((int) max(1, (int) $item->quantity)).' units') })"
                                            x-init="if (@js($canEditInquiryProducts && $isDraftInquiryItem)) editing = true"
                                            :class="{ 'is-inline-saving': status === 'saving', 'is-inline-error': status === 'error' }"
                                        >
                                            <span x-show="!editing" class="ft-order-product-edit-value" x-text="display">{{ number_format((int) max(1, (int) $item->quantity)) }} units</span>
                                            @if($canEditInquiryProducts)
                                                <button x-show="!editing" :disabled="status === 'saving' || categorySaving || productSaving || priceSaving || notesSaving" type="button" class="ft-inline-edit-button" title="Edit quantity" aria-label="Edit product quantity" x-on:click.stop="if (beginEdit()) $nextTick(() => { $refs.quantityInput.focus(); $refs.quantityInput.select(); })">✎</button>
                                                <input x-ref="quantityInput" data-inquiry-item-quantity x-cloak x-show="editing" x-model="draftValue" class="ft-order-product-inline-input ft-order-product-number-input" type="number" min="1" max="999999999" step="1" :disabled="categorySaving || productSaving"
                                                    x-on:keydown.escape.prevent="cancelEdit()"
                                                    x-on:keydown.enter.prevent="$event.target.blur()"
                                                    x-on:blur="if (editing && !categorySaving && !productSaving && !quantitySaving) { const next = positiveInteger(draftValue); quantitySaving = true; commit(next, Number(next).toLocaleString() + ' units', () => $wire.updateInquiryItem({{ $item->id }}, 'quantity', next)).then(async (ok) => { quantitySaving = false; if (ok && @js($isDraftInquiryItem)) await $wire.$refresh(); else if (!ok) editing = true; }) }"
                                                >
                                                <x-ui.inline-save-state compact />
                                            @endif
                                        </div>
                                    </td>
                                    <td class="ft-order-product-price" data-label="Unit price">
                                        <div class="ft-inline-field-editor ft-inline-edit-shell" x-data="window.FlowTrack.ui.inlineEdit({ key: @js('inquiry-item-'.$item->id.'-unit-price'), label: 'unit price', value: @js($unitPriceValue), display: @js($unitPriceDisplay) })" :class="{ 'is-inline-saving': status === 'saving', 'is-inline-error': status === 'error' }">
                                            <span x-show="!editing" class="ft-order-product-edit-value" x-text="display">{{ $unitPriceDisplay }}</span>
                                            @if($canEditInquiryProducts)
                                                <button x-show="!editing" :disabled="status === 'saving' || categorySaving || productSaving || quantitySaving || notesSaving" type="button" class="ft-inline-edit-button" title="Edit unit price" aria-label="Edit unit price" x-on:click.stop="if (beginEdit()) $nextTick(() => { $refs.priceInput.focus(); $refs.priceInput.select(); })">✎</button>
                                                <div x-cloak x-show="editing" class="ft-order-product-price-input-wrap">
                                                    <span>{{ $inquiryCurrencySymbol }}</span>
                                                    <input x-ref="priceInput" x-model="draftValue" class="ft-order-product-inline-input ft-order-product-number-input" type="number" min="0" step="0.01"
                                                        x-on:keydown.escape.prevent="cancelEdit()"
                                                        x-on:keydown.enter.prevent="$event.target.blur()"
                                                        x-on:blur="if (editing && !priceSaving) { const raw = String(draftValue ?? '').trim(); const parsed = raw === '' ? '' : Number(raw); const next = raw === '' ? '' : (Number.isFinite(parsed) ? Math.max(0, parsed).toFixed(2) : ''); priceSaving = true; commit(next, next === '' ? '—' : @js($inquiryCurrencySymbol) + Number(next).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}), () => $wire.updateInquiryItem({{ $item->id }}, 'unit_price', next)).then((ok) => { priceSaving = false; if (!ok) editing = true; }) }"
                                                    >
                                                </div>
                                                <x-ui.inline-save-state compact />
                                            @endif
                                        </div>
                                    </td>
                                    <td class="ft-order-product-notes" data-label="Notes">
                                        <div class="ft-inline-field-editor ft-inline-edit-shell" x-data="window.FlowTrack.ui.inlineEdit({ key: @js('inquiry-item-'.$item->id.'-notes'), label: 'product notes', value: @js($item->notes ?? ''), display: @js($item->notes ?: 'Add notes') })" :class="{ 'is-inline-saving': status === 'saving', 'is-inline-error': status === 'error' }">
                                            <span x-show="!editing" class="ft-order-product-note-value" :class="{ 'is-empty': !value }" x-text="display">{{ $item->notes ?: 'Add notes' }}</span>
                                            @if($canEditInquiryProducts)
                                                <button x-show="!editing" :disabled="status === 'saving' || categorySaving || productSaving || quantitySaving || priceSaving" type="button" class="ft-inline-edit-button" title="Edit notes" aria-label="Edit product notes" x-on:click.stop="if (beginEdit()) $nextTick(() => { $refs.notesInput.focus(); $refs.notesInput.select(); })">✎</button>
                                                <input x-ref="notesInput" x-cloak x-show="editing" x-model="draftValue" class="ft-order-product-inline-input ft-order-product-notes-input" type="text" maxlength="2000" placeholder="Product notes"
                                                    x-on:keydown.escape.prevent="cancelEdit()"
                                                    x-on:keydown.enter.prevent="$event.target.blur()"
                                                    x-on:blur="if (editing && !notesSaving) { const next = String(draftValue || '').trim(); notesSaving = true; commit(next, next || 'Add notes', () => $wire.updateInquiryItem({{ $item->id }}, 'notes', next)).then((ok) => { notesSaving = false; if (!ok) editing = true; }) }"
                                                >
                                                <x-ui.inline-save-state compact />
                                            @endif
                                        </div>
                                    </td>
                                    <x-catalog.detail-product-updated
                                        :primary="$updatedDate"
                                        :secondary="$updatedTime"
                                    />
                                    <td class="ft-order-product-actions-cell" data-label="Actions">
                                        <x-catalog.detail-product-actions
                                            :item-id="$item->id"
                                            :can-delete="$canDeleteInquiryProducts"
                                            remove-method="removeInquiryItem"
                                            confirm-text="Remove this product from the Inquiry?"
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                        <x-slot:afterTable>
                            @if($showAddInquiryProductForm && $canCreateInquiryProducts)
                                <div class="ft-detail-products-inline-add" wire:key="inquiry-add-product-inline-{{ $inquiry->id }}" x-data x-on:keydown.escape.window="$wire.closeAddInquiryProductForm()">
                                    <x-catalog.detail-add-product
                                        :wire-key="'inquiry-detail-add-product-'.$inquiry->id"
                                        search-model="inquiryProductSearch"
                                        :search-value="$inquiryProductSearch"
                                        :search-results="$inquiryProductSearchResults"
                                        :result-total="$inquiryProductResultTotal"
                                        show-all-method="showAllInquiryProductResults"
                                        select-method="selectInquiryProduct"
                                        :selected-product="$inquiryProductSelectedProduct"
                                        :category-value="$inquiryProductCategory"
                                        quantity-model="inquiryProductQuantity"
                                        unit-price-model="inquiryProductUnitPrice"
                                        :currency-symbol="$inquiryCurrencySymbol"
                                        close-method="closeAddInquiryProductForm"
                                        save-method="saveInquiryProduct"
                                        selected-error-key="inquiryProductSelectedId"
                                        quantity-error-key="inquiryProductQuantity"
                                        unit-price-error-key="inquiryProductUnitPrice"
                                        record-label="Inquiry"
                                    />
                                </div>
                            @endif
                        </x-slot:afterTable>

                        <x-slot:footer>
                            <span>Product and quantity changes are recorded in inquiry activity.</span>
                            @if($canCreateInquiryProducts && !$showAddInquiryProductForm)
                                <button type="button" class="ft-outline-btn ft-order-product-add-another" wire:click="openAddInquiryProductForm" wire:loading.attr="disabled" wire:target="openAddInquiryProductForm">＋ Add another product</button>
                            @endif
                        </x-slot:footer>
                    </x-catalog.detail-products-card>
                    @endif

                    <div id="tab-workflow" class="ft-inquiry-overview-taskflow ft-inquiry-workflow-pane">
                        @if(auth()->user()->canModule('tasks', 'view'))
                            @include('livewire.inquiries._taskflow')
                        @else
                            <section class="panel"><div class="ft-inquiry-empty-workflow">Task access is not enabled for your role.</div></section>
                        @endif
                    </div>

                    @if(auth()->user()->canModule('documents', 'view'))@include('livewire.inquiries._attachments')@endif
                    @include('livewire.inquiries._activity')
                </div>
            @endif

            @if($showTaskDocumentModal && $taskDocumentModalTask)
                @php
                    $completeAfterTaskDocument = (int) ($pendingCompletionTaskId ?? 0) === (int) $taskDocumentModalTask->id;
                @endphp
                <div class="ft-inquiry-task-document-modal-backdrop" wire:key="inquiry-task-document-modal" wire:click.self="closeTaskDocumentModal">
                    <section class="ft-inquiry-task-document-modal" role="dialog" aria-modal="true" aria-labelledby="task-document-modal-title">
                        <header class="ft-inquiry-task-document-modal-head">
                            <div>
                                <h2 id="task-document-modal-title">{{ $completeAfterTaskDocument ? 'Required file needed to complete task' : 'Add new document to task' }}</h2>
                                <p>{{ $completeAfterTaskDocument ? 'Add the required file now. The task will be completed automatically after the document is saved.' : 'Upload a new file or choose a document that already exists.' }}</p>
                            </div>
                            <button type="button" class="ft-inquiry-task-document-modal-close" wire:click="closeTaskDocumentModal" aria-label="Close">×</button>
                        </header>

                        <div class="ft-inquiry-task-document-modal-body">
                            <div class="ft-inquiry-task-document-target">
                                <span class="ft-inquiry-task-document-target-icon">▣</span>
                                <div>
                                    <small>ATTACHING TO</small>
                                    <strong>{{ $taskDocumentModalTask->title }}</strong>
                                    <span>INQ-TASK-{{ str_pad((string) $taskDocumentModalTask->id, 5, '0', STR_PAD_LEFT) }} &nbsp;·&nbsp; {{ $inquiry->sourceWorkflow?->name ?? 'Inquiry Taskflow' }}</span>
                                    <span class="ft-inquiry-task-document-reference"><b>Inquiry Reference:</b> {{ $inquiry->reference_number ?: '—' }}</span>
                                </div>
                                <span class="ft-inquiry-task-document-target-lock">▣&nbsp; Task selected</span>
                            </div>

                            <div class="ft-inquiry-task-document-source-label">Document source</div>
                            <div class="ft-inquiry-task-document-source-tabs">
                                <button type="button" class="{{ $taskDocumentSource === 'upload' ? 'active' : '' }}" wire:click="setTaskDocumentSource('upload')" @disabled(!$canCreateDocuments)>
                                    <span>↥</span> Upload new
                                </button>
                                <button type="button" class="{{ $taskDocumentSource === 'existing' ? 'active' : '' }}" wire:click="setTaskDocumentSource('existing')" @disabled(!$canLinkDocuments)>
                                    <span>▤</span> Choose existing
                                </button>
                            </div>

                            @if($taskDocumentSource === 'upload' && $canCreateDocuments)
                                <label class="ft-inquiry-task-document-dropzone">
                                    <input type="file" wire:model="taskDocumentUpload" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip,.txt,.csv,.ai,.eps,.esp">
                                    <span class="ft-inquiry-task-document-upload-icon">⇧</span>
                                    @if($taskDocumentUpload)
                                        <strong>{{ $taskDocumentUpload->getClientOriginalName() }}</strong>
                                        <b>File selected — choose another file</b>
                                        <small>{{ number_format(max(1, (int) ceil($taskDocumentUpload->getSize() / 1024))) }} KB · ready to add</small>
                                    @else
                                        <strong>Drop a file here</strong>
                                        <b>or browse files</b>
                                        <small>PDF, Office files, JPG, PNG, ZIP, AI, EPS or ESP · Max 20 MB</small>
                                    @endif
                                </label>
                                @error('taskDocumentUpload')<p class="ft-inquiry-task-document-error">{{ $message }}</p>@enderror
                            @else
                                <div class="ft-inquiry-task-document-existing">
                                    @if($availableTaskDocuments->isEmpty())
                                        <div class="ft-inquiry-task-document-existing-empty">No existing client documents are available.</div>
                                    @else
                                        <label>
                                            <span>Choose an existing document</span>
                                            <select wire:model="taskExistingDocumentId">
                                                <option value="">Select a document...</option>
                                                @foreach($availableTaskDocuments as $sourceDocument)
                                                    <option value="{{ $sourceDocument->id }}">{{ $sourceDocument->name }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                    @endif
                                </div>
                                @error('taskExistingDocumentId')<p class="ft-inquiry-task-document-error">{{ $message }}</p>@enderror
                            @endif

                            <label class="ft-inquiry-task-document-note">
                                <span>Document note (optional)</span>
                                <input type="text" wire:model="taskDocumentNote" placeholder="Add a short note about this document...">
                            </label>
                            @error('taskDocumentNote')<p class="ft-inquiry-task-document-error">{{ $message }}</p>@enderror

                            <div class="ft-inquiry-task-document-info">
                                <span>ⓘ</span>
                                <p>
                                    This document will appear directly under <strong>{{ $taskDocumentModalTask->title }}</strong>.
                                    @if($completeAfterTaskDocument) Saving it will also mark the task as Completed. @elseif($taskDocumentModalTask->completed_at) Adding a document will not reopen or change the completed task. @endif
                                </p>
                            </div>
                        </div>

                        <footer class="ft-inquiry-task-document-modal-actions">
                            <button type="button" class="secondary" wire:click="closeTaskDocumentModal">Cancel</button>
                            <button type="button" class="primary" wire:click="saveTaskDocument" wire:loading.attr="disabled" wire:target="saveTaskDocument,taskDocumentUpload"
                                @disabled($taskDocumentSource === 'upload' ? !$taskDocumentUpload : !$taskExistingDocumentId)>
                                <span wire:loading.remove wire:target="saveTaskDocument">{{ $completeAfterTaskDocument ? 'Add file & complete' : 'Add document' }}</span>
                                <span wire:loading wire:target="saveTaskDocument">{{ $completeAfterTaskDocument ? 'Adding & completing...' : 'Adding...' }}</span>
                            </button>
                        </footer>
                    </section>
                </div>
            @endif

            @if($showInquiryAttentionModal)
                <div class="ft-inquiry-attention-modal-backdrop" wire:key="inquiry-attention-modal" wire:click.self="closeInquiryAttentionReason">
                    <section class="ft-inquiry-attention-modal" role="dialog" aria-modal="true" aria-labelledby="inquiry-attention-modal-title">
                        <header class="ft-inquiry-attention-modal-head">
                            <div>
                                <h2 id="inquiry-attention-modal-title">Request attention</h2>
                                <p>{{ $inquiry->inquiry_number }} · Admin, Super Admin and the Inquiry creator will be notified.</p>
                            </div>
                            <button type="button" class="ft-inquiry-attention-modal-close" wire:click="closeInquiryAttentionReason" aria-label="Close">×</button>
                        </header>
                        <div class="ft-inquiry-attention-modal-body ft-mention-host">
                            <label for="inquiry-attention-reason">Reason for flag *</label>
                            <textarea id="inquiry-attention-reason" class="ft-mention-input" wire:model="inquiryAttentionReason" rows="5" maxlength="2000" autocomplete="off" data-mention-users="{{ json_encode($inquiryMentionUsers->values()->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" placeholder="Explain what needs attention. Type @ to mention a user..."></textarea>
                            @error('inquiryAttentionReason')<p class="ft-inquiry-attention-modal-error">{{ $message }}</p>@enderror
                            <p class="ft-inquiry-attention-modal-help">The reason is added to Inquiry comments. Use <b>@</b> to mention specific users in addition to the automatic Admin/Super Admin/creator notification.</p>
                        </div>
                        <footer class="ft-inquiry-attention-modal-actions">
                            @if($inquiry->needs_attention)<button type="button" class="ft-inquiry-attention-clear" wire:click="clearInquiryAttention" wire:loading.attr="disabled" wire:target="clearInquiryAttention">Clear flag</button>@else<span></span>@endif
                            <div>
                                <button type="button" class="secondary" wire:click="closeInquiryAttentionReason">Cancel</button>
                                <button type="button" class="primary" wire:click="saveInquiryAttentionReason" wire:loading.attr="disabled" wire:target="saveInquiryAttentionReason">
                                    <span wire:loading.remove wire:target="saveInquiryAttentionReason">Request attention</span>
                                    <span wire:loading wire:target="saveInquiryAttentionReason">Saving...</span>
                                </button>
                            </div>
                        </footer>
                    </section>
                </div>
            @endif

            @if($showTaskAttentionModal && $taskAttentionTaskId)
                @php
                    $attentionTask = $inquiry->tasks->firstWhere('id', (int) $taskAttentionTaskId);
                @endphp
                <div class="ft-inquiry-attention-modal-backdrop" wire:key="inquiry-task-attention-modal" wire:click.self="closeTaskAttentionReason">
                    <section class="ft-inquiry-attention-modal" role="dialog" aria-modal="true" aria-labelledby="task-attention-modal-title">
                        <header class="ft-inquiry-attention-modal-head">
                            <div>
                                <h2 id="task-attention-modal-title">Why is attention required?</h2>
                                <p>{{ $attentionTask?->title ?: 'Inquiry task' }} · {{ $attentionTask?->status ?: 'Attention required' }}</p>
                            </div>
                            <button type="button" class="ft-inquiry-attention-modal-close" wire:click="closeTaskAttentionReason" aria-label="Close">×</button>
                        </header>
                        <div class="ft-inquiry-attention-modal-body ft-mention-host">
                            <label for="inquiry-task-attention-reason">Reason for flag *</label>
                            <textarea id="inquiry-task-attention-reason" class="ft-mention-input" wire:model="taskAttentionReason" rows="5" maxlength="2000" autocomplete="off" data-mention-users="{{ json_encode($inquiryMentionUsers->values()->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" placeholder="Explain what is blocking the task or what needs attention. Type @ to mention a user..."></textarea>
                            @error('taskAttentionReason')<p class="ft-inquiry-attention-modal-error">{{ $message }}</p>@enderror
                            <p class="ft-inquiry-attention-modal-help">Saving this reason adds it to Inquiry comments, notifies Admin/Super Admin/the Inquiry creator, and supports <b>@mentions</b>.</p>
                        </div>
                        <footer class="ft-inquiry-attention-modal-actions">
                            <button type="button" class="secondary" wire:click="closeTaskAttentionReason">Cancel</button>
                            <button type="button" class="primary" wire:click="saveTaskAttentionReason" wire:loading.attr="disabled" wire:target="saveTaskAttentionReason">
                                <span wire:loading.remove wire:target="saveTaskAttentionReason">Save reason</span>
                                <span wire:loading wire:target="saveTaskAttentionReason">Saving...</span>
                            </button>
                        </footer>
                    </section>
                </div>
            @endif

        </section>
