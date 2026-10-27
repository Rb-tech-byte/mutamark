# ERP API contracts

The application mounts these routes at `/api`; when installed in a subdirectory, use its configured base URL (for example `/mutadash/api`). Send `Accept: application/json`. Obtain a Sanctum bearer token with POST `/api/login` using existing credentials. API login is limited to five attempts per minute; authenticated core routes are limited to 60 requests per minute. Disabled accounts are denied. Request metadata is logged without tokens or payloads.

## Existing domain data

GET `/api/v1/{resource}` and GET `/api/v1/{resource}/{id}` support customers, suppliers, products, inventory, sales-orders, sales-invoices, purchase-invoices, payments, accounting (journal entries), and employees. Resources use existing models and permissions; company and available own/any restrictions apply. Lists accept page/per_page, with a maximum of 100 rows. Responses use the existing ApiResponseTrait (`success`, `message`, `data.items`, `data.pagination`). Detail responses use `data` for the selected safe fields. Arbitrary model names and arbitrary fields are not accepted.

Core reads use these contracts. The existing controllers also expose the writes listed below; bulk import/export coverage is not complete. Existing Lead, Taskly and Hrm APIs remain in their packages.

## Documents

- GET/POST `/api/v1/documents`: list/upload. Upload multipart fields: title, file, access_level (company/private); optional category, description, expiry_date, entity_type/entity_id, is_confidential. Allowed files: PDF, Word, Excel, CSV, text, JPEG/PNG/WebP, up to 20 MB. Both detected MIME and original extension are validated. Files are stored privately. Malware scanning is still an operational integration requirement.
- GET `/api/v1/documents/{document}`: metadata and attributed version history.
- POST `/api/v1/documents/{document}/versions`: multipart file and required change_notes. Replacements require ownership or manage-any-media in addition to create-media.
- GET `/api/v1/documents/{document}/download/{version?}`: authenticated attachment download of latest or historical version.
- DELETE `/api/v1/documents/{document}`: archive with retained files/versions; delete-media and ownership/any-media are required.

Document lists/details are normal JSON/paginator responses. Upload returns 201. This reuses media permissions; private/confidential documents remain visible only to their author.

## Proformas

- GET `/api/v1/proformas`: paginated list, optional search.
- POST `/api/v1/proformas`: customer_id, issue_date, optional expiry_date, currency=TZS, payment_terms/notes; items contain product_id, numeric quantity (up to four decimal places), unit_price, optional discount_percentage/tax_rate. Customer and product identities are company-scoped. Server computes names and totals; client totals are ignored.
- POST `/api/v1/proformas/{proforma}/convert`: warehouse_id, invoice_date, due_date. Creates a draft in the existing sales invoice module. Retries return the same invoice. Expired/cancelled proformas cannot convert. Fractional quantities are preserved in the expanded invoice/stock workflow; conversion remains TZS-only.
- POST `/api/v1/proformas/{proforma}/cancel`: cancels an unconverted active proforma.

## Physical counts

Reuse the existing Stock Adjustments cycle_count workflow rather than creating a second stocktake engine.

- GET/POST `/api/v1/stock-adjustments`: list/create. Creation fields match the existing UI: warehouse_id, adjustment_type, adjustment_date, reason, items[{product_id,counted_quantity,remarks?}].
- GET `/api/v1/stock-adjustments/scan?warehouse_id=...&code=...`: lookup scoped SKU or registered serial/barcode for the current warehouse.
- POST `/api/v1/stock-adjustments/{stockAdjustment}/post`: reconcile once. Posting rejects stale stock snapshots instead of overwriting newer movements.

## Approvals and delegation

- POST `/api/v1/approvals/{id}/action`: action and optional comments. Supports review, return, approve, reject, sign, issue, close. The assigned actor or current active delegate must perform a legal transition. Self-approval/signing/issuance is prohibited where policy requires segregation.
- POST `/api/v1/approval-delegations`: delegate_id, start_date, end_date, reason, optional document_type. Delegator is always the current user.
- DELETE `/api/v1/approval-delegations/{delegation}`: revoke only your own delegation.

## Payment confirmation

Customer checkout submissions record a reference as pending_verification. They never constitute proof of payment. The public success/receipt page is available only after verified settlement.

POST `/api/v1/payments/webhook/{gateway}` is the existing **web** route (not an authenticated core API route). It accepts a generic integration contract, not a claim of native compatibility with every provider: X-Payment-Signature must equal the lowercase hexadecimal HMAC-SHA256 of the exact raw request body using the gateway webhook_secret. Fields: transaction_reference (or reference), status=completed, amount, currency, gateway_transaction_id, optional payment_channel. Gateway, company, amount and currency must match. Real gateway adapters must translate their native signed callbacks into an equally verified contract.

Settlement is locked and transactional, rejects duplicate provider references and overpayment, creates an existing Account customer-payment allocation and calls existing journal/bank services. Gateway credentials must contain bank_account_id for an active bank account with a GL account in the invoice company. The current flow accepts TZS, a posted invoice and zero configured fees. Advances, authorized overpayment, FX and fee-account posting remain incomplete; these cases fail validation rather than silently corrupt balances.

## Schema and release preparation

Run `php artisan srs:audit --json` for a read-only schema/integrity preflight. With explicit user approval, local maintenance converted 292 MyISAM tables to InnoDB with exact row-count checks and applied the shared numbering/document archive and warehouse-stock uniqueness migrations. The application is open. See `scratch/srs-storage-upgrade-history.json` and `scratch/srs-db-audit-after.json`. The upgrade does not recreate foreign keys omitted by MyISAM.

`php artisan srs:backup` creates and authenticates an encrypted online full snapshot; `--verify=FILENAME.sql.enc` authenticates an existing snapshot without restoring it. `php artisan srs:rehearse-backup FILENAME.sql.enc` restores only into a newly created random temporary database and removes that database afterward. An isolated rehearsal restored 293 tables successfully; evidence is in `scratch/srs-restore-rehearsal.json`. Preserve APP_KEY separately for recovery. Daily 02:00 Africa/Dar_es_Salaam scheduling is defined in the application; an operating-system scheduler and offsite disk must still be configured. Incremental backups/PITR and production recovery targets are not implemented.

Validation: `php vendor/phpunit/phpunit/phpunit -c phpunit.srs.xml` uses an explicitly isolated in-memory SQLite database. Do not change its connection to the business database. See SRS_REVIEW.md for remaining requirements and acceptance limits.

## Extended ERP write contracts

All paths below begin with `/api/v1`, require Sanctum authentication, an enabled account, the corresponding existing module permission and company ownership. Send `Accept: application/json`. Existing FormRequests validate inputs; permission failures return 403, ownership failures 404, invalid amounts/relationships 422 and conflicting workflow states 409. Existing module forms remain the authoritative required-field definitions. Core store/update actions return the existing model JSON (201/200), rather than the generic list envelope.

- POST `/customers`, PUT `/customers/{customer}`; POST `/suppliers`, PUT `/suppliers/{vendor}`; POST `/products`, PUT `/products/{item}`. Related users/categories/taxes/units/warehouses are company-scoped. Optional customer `credit_limit` is nullable (unlimited) or nonnegative; changes require manage-account. Product quantities cannot be overwritten by editing a master: use existing receipts/counts/transfers.
- POST `/sales-invoices`, PUT `/sales-invoices/{salesInvoice}` and the equivalent `/purchase-invoices/{purchaseInvoice}`. Draft writes are atomic; quantities are numeric. POST `/{sales-invoices|purchase-invoices}/{id}/post` uses existing stock/accounting listeners inside a locked transaction. Customer credit limits apply before sales posting; closed accounting periods reject journals.
- POST `/sales-orders`; POST `/sales-orders/{id}/reserve`; POST `/sales-orders/{id}/deliver` with optional `items[{id,quantity}]`; POST `/sales-orders/{id}/invoice`. Delivery quantities must be pending on that order; repeated invoice conversion returns the existing linked invoice.
- POST `/goods-received-notes/{id}/post`; POST `/purchase-orders/{purchaseOrder}/action` with the existing issue/invoice action fields. Receipts are locked, bounded by the issued order and counted once. PO-linked purchase invoices do not receive stock a second time. Supplier payment remains held until receipt/invoice matching passes.
- POST `/customer-payments`, PATCH `/customer-payments/{customerPayment}/status`; POST `/supplier-payments`, PATCH `/supplier-payments/{vendorPayment}/status`. Use existing receipt/payment form fields and allocations. Status is cleared or cancelled. Finalized changes require reversal; repeating the same final status is a no-op. Allocations must equal cash plus applied notes plus supplier withholding. Supplier fields `wht_base_amount` and `wht_rate` are validated; withholding is recomputed server-side.
- GET `/tax-summary`; POST `/withholding/{id}/remit` with `bank_account_id`, `remittance_date`. Remittance journals and bank movements post once. Configure the withholding liability GL first.
- POST `/accounting/controls` with financial-year dates, accounting_locked_until and the existing inventory adjustment/payroll deductions/loan receivable/withholding payable GL mappings. Requires manage-account; audit history records changes.
- POST `/sales-invoices/{salesInvoice}/reconcile` with a required reason. Recomputes derived balances from existing cleared customer allocations, preserves cash records and records before/after audit. Requires manage-account; rejects overallocated invoices for reviewed correction.
- POST `/supplier/orders/{purchaseOrder}/accept`; POST `/supplier/orders/{purchaseOrder}/attachments` (multipart existing DMS fields); PATCH `/supplier/profile` (allowed contact fields). Supplier identity and PO ownership are checked; uploads remain private.

POST `/api/two-factor` accepts `action` (setup, confirm, disable), the current `password`, and `code` for confirm/disable. Setup returns the authenticator secret/URI; confirmation returns recovery codes once. API/web login verifies TOTP/recovery codes when enabled; short-lived code replay is rejected. Gateway credentials and two-factor secrets are encrypted and omitted from ordinary serialization.

Public verification distinguishes legacy registry records from version 2 source-bound signatures. A modified source or signed payload invalidates version 2 verification; it is not an offline certificate authority.
