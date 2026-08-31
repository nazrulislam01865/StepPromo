<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="color-scheme" content="light">
    <title>{{ $invitation->inquiry->inquiry_number }} · Supplier quotation</title>
    @if($brand['favicon_url'] ?? null)<link rel="icon" href="{{ $brand['favicon_url'] }}">@endif
    <script
        src="{{ asset('js/flowtrack-image-fallback.js') }}?v={{ \App\Support\FrontendBuildVersion::current() }}"
        data-fallback-src="{{ asset('images/flowtrack-image-fallback.svg') }}"
    ></script>
    @vite(['resources/theme/flowtrack/core.css', 'resources/css/app.css'])
</head>
<body class="ft-rfq-portal-page">
    <x-rfq.public.header :brand="$brand" :supplier="$invitation->supplier" :buyer-email="$buyerEmail" />

    <main class="ft-rfq-portal-shell">
        <section class="ft-rfq-portal-main">
            <div class="ft-rfq-portal-title-row">
                <div>
                    <a class="ft-rfq-portal-backlink" href="{{ route('rfq.public.show', ['token' => $token, 'step' => 'details']) }}">
                        <x-rfq.public.icon name="arrow-left" /> Quotation request
                    </a>
                    <h1>Submit your quotation</h1>
                    <p>Provide your best commercial offer for the product below.</p>
                </div>
                <div class="ft-rfq-portal-save-state" aria-live="polite">
                    <span class="ft-rfq-portal-draft-pill">{{ $submitted ? 'Submitted' : 'Draft' }}</span>
                    @if($savedAt)
                        <small><x-rfq.public.icon name="clock" /> Saved {{ $savedAt->diffForHumans() }}</small>
                    @else
                        <small><x-rfq.public.icon name="clock" /> Not saved yet</small>
                    @endif
                </div>
            </div>

            <div class="ft-rfq-portal-private-note">
                <span class="ft-rfq-portal-lock-icon"><x-rfq.public.icon name="lock" /></span>
                <span>This quotation is private and can only be accessed through your invitation link.</span>
            </div>

            @if(session('success'))
                <div class="ft-rfq-portal-feedback is-success" role="status">
                    <x-rfq.public.icon name="check" />{{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="ft-rfq-portal-feedback is-error" role="alert">
                    <x-rfq.public.icon name="alert" />{{ $errors->first() }}
                </div>
            @endif

            <x-rfq.public.stepper :steps="$steps" :token="$token" :locked="$locked" />

            @switch($step)
                @case('pricing')
                    <x-rfq.public.pricing
                        :invitation="$invitation"
                        :token="$token"
                        :quote="$quote"
                        :products="$products"
                        :currency="$currency"
                        :locked="$locked"
                    />
                    @break
                @case('documents')
                    <x-rfq.public.documents
                        :invitation="$invitation"
                        :token="$token"
                        :quote="$quote"
                        :products="$products"
                        :documents="$documents"
                        :document-types="$documentTypes"
                        :required-document-types="$requiredDocumentTypes"
                        :required-document-count="$requiredDocumentCount"
                        :required-document-total="$requiredDocumentTotal"
                        :supporting-information-options="$supportingInformationOptions"
                        :supporting-information="$supportingInformation"
                        :locked="$locked"
                    />
                    @break
                @case('review')
                    <x-rfq.public.review
                        :invitation="$invitation"
                        :token="$token"
                        :quote="$quote"
                        :products="$products"
                        :documents="$documents"
                        :document-types="$documentTypes"
                        :contact="$contact"
                        :rfq-reference="$rfqReference"
                        :currency="$currency"
                        :product-subtotal="$productSubtotal"
                        :sample-cost="$sampleCost"
                        :other-costs="$otherCosts"
                        :total-quoted-value="$totalQuotedValue"
                        :client-name="$clientName"
                        :ready-to-submit="$readyToSubmit"
                        :locked="$locked"
                        :submitted="$submitted"
                    />
                    @break
                @default
                    <x-rfq.public.details
                        :invitation="$invitation"
                        :token="$token"
                        :quote="$quote"
                        :products="$products"
                        :contact="$contact"
                        :rfq-reference="$rfqReference"
                        :locked="$locked"
                    />
            @endswitch
        </section>

        <aside class="ft-rfq-portal-aside">
            <x-rfq.public.summary
                :invitation="$invitation"
                :token="$token"
                :step="$step"
                :quote="$quote"
                :first-product="$firstProduct"
                :currency="$currency"
                :total-quantity="$totalQuantity"
                :product-subtotal="$productSubtotal"
                :sample-cost="$sampleCost"
                :other-costs="$otherCosts"
                :total-quoted-value="$totalQuotedValue"
                :details-complete="$detailsComplete"
                :pricing-complete="$pricingComplete"
                :documents="$documents"
                :documents-complete="$documentsComplete"
                :ready-to-submit="$readyToSubmit"
                :locked="$locked"
                :submitted="$submitted"
            />
        </aside>
    </main>

    <x-rfq.public.footer :brand="$brand" :buyer-email="$buyerEmail" />

    <script>
    (() => {
        const upload = document.querySelector('[data-rfq-document-input]');
        if (upload) {
            upload.addEventListener('change', () => {
                if (upload.files && upload.files.length && upload.form) {
                    upload.form.action = upload.dataset.uploadUrl || upload.form.action;
                    upload.form.submit();
                }
            });
        }
        const dropZone = document.querySelector('[data-rfq-dropzone]');
        if (dropZone && upload) {
            ['dragenter', 'dragover'].forEach(name => dropZone.addEventListener(name, event => {
                event.preventDefault();
                dropZone.classList.add('is-dragging');
            }));
            ['dragleave', 'drop'].forEach(name => dropZone.addEventListener(name, event => {
                event.preventDefault();
                dropZone.classList.remove('is-dragging');
            }));
            dropZone.addEventListener('drop', event => {
                if (!event.dataTransfer?.files?.length) return;
                const transfer = new DataTransfer();
                Array.from(event.dataTransfer.files).forEach(file => transfer.items.add(file));
                upload.files = transfer.files;
                if (upload.form) {
                    upload.form.action = upload.dataset.uploadUrl || upload.form.action;
                    upload.form.submit();
                }
            });
        }

        const pricing = document.getElementById('rfq-pricing-form');
        if (pricing) {
            const currency = pricing.querySelector('[data-rfq-currency]');
            const totalNodes = document.querySelectorAll('[data-rfq-live-total]');
            const recalculate = () => {
                let subtotal = 0;
                pricing.querySelectorAll('[data-rfq-price]').forEach(input => {
                    const row = input.closest('[data-rfq-price-row]');
                    subtotal += (Number(row?.dataset.quantity || 0) * Number(input.value || 0));
                });
                const money = name => Number(pricing.querySelector(`[name="${name}"]`)?.value || 0);
                const total = subtotal + money('tooling_cost') + money('sample_cost') + money('freight') - money('discount');
                const code = currency?.value || 'USD';
                totalNodes.forEach(node => node.textContent = `${code} ${total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
            };
            pricing.addEventListener('input', recalculate);
            pricing.addEventListener('change', recalculate);
            recalculate();
        }

        document.querySelectorAll('[data-rfq-decline]').forEach(button => button.addEventListener('click', event => {
            if (!window.confirm('Decline this request for quotation?')) event.preventDefault();
        }));
    })();
    </script>
</body>
</html>
