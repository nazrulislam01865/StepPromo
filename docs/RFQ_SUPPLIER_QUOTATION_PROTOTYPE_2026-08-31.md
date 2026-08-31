# Supplier quotation portal implementation

Date: 2026-08-31

## Scope

The public supplier RFQ flow has been rebuilt as a modular four-step quotation portal. The supplied prototype is the visual source of truth for the **Documents** and **Review & submit** screens. The same shell, spacing system, typography, cards, stepper, summary sidebar, footer, and action patterns are shared across the other steps so the experience stays consistent.

## Architecture

- `resources/views/rfq/public-show.blade.php` is the page shell only.
- `resources/views/components/rfq/public/` contains the header, stepper, detail form, pricing form, document form, review, summary, footer, product thumbnail, and icon components.
- `resources/css/modules/application/25-public-rfq-quotation-prototype.css` contains isolated public-portal styles under `ft-rfq-*` selectors.
- `app/Services/Inquiries/PublicRfqPortalService.php` owns draft preparation, totals, completion state, product presentation, document storage, and step persistence.
- `app/Services/Inquiries/InquiryRfqService.php` keeps the existing final submission activity/email behavior and now accepts the saved multi-step draft.

## Persistence added

The quote stores supplier contact details, tooling/sample/discount values, tax status, lead times, Incoterm, shipping port, estimated delivery, specification compliance, supporting-information selections, document notes, and submitter details. Quote items now store MOQ. Supplier RFQ attachments are stored in the new `inquiry_rfq_quote_documents` table.

Migration:

`database/migrations/2026_08_31_173000_expand_inquiry_rfq_quotes_for_supplier_portal.php`

## Upload and access rules

Uploads accept PDF, XLSX, DOCX, JPG/JPEG, and PNG up to 20 MB per file. Files pass through the project's existing quarantine/security storage service and are kept on private document storage. Preview, download, remove, and product-image routes all validate the invitation token and record ownership before serving a file.

The two required document types are:

- Formal quotation
- Price breakdown

The supplier can reclassify uploaded documents from the table before continuing.

## Deployment

Run the normal deployment sequence so the new database fields/table exist before serving the updated public RFQ page:

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan optimize
php artisan queue:restart
php artisan reverb:restart
```

The archive did not contain `vendor/` or `node_modules/`, so the source CSS module is included and the existing prebuilt `public/build/assets/app-BHJXvmsU.css` was also updated with the same isolated portal module. A future normal frontend build will regenerate that asset from `resources/css/app.css`.

## Verification

`tests/Feature/PublicSupplierQuotationPortalTest.php` checks the modular component composition, prototype sections, isolated stylesheet, secure document routes/storage integration, and draft persistence wiring.

## 2026-08-31 active-step hotfix

Fixed the public RFQ step-state builder so the current `$step` is captured safely when computing the stepper's `active` state. The builder now uses an arrow function, which automatically captures the surrounding completion-state variables and the active step and prevents the `Undefined variable $step` runtime error seen on the public RFQ GET route.
