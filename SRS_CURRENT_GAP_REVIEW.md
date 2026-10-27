# Mutamark ERP SRS review and implementation evidence — 14 October 2026

## Implemented in the existing system

This implementation extends the existing Laravel/React modules, AuthenticatedLayout, shared controls, translations, JavaScript and theme. It does not introduce a replacement ERP or subscription product. Full compliance with all 35 SRS modules is **not certified**; the remaining acceptance gaps below remain open.

| Review finding | Implemented behavior | Evidence / limits |
|---|---|---|
| G01 Invoice balances | Controlled, audited balance reconciliation from cleared allocations; invoice 1 paid amount corrected from 1,700,000 to 450,000 | Existing receipts, bank and gateway records retained. Three unallocated historical gateway completions retained as encrypted exceptions for finance review; actual bank evidence is still required. |
| G02 Manual payments | One locked transaction for allocations, notes, journals and bank movements; repeated clearance is a no-op; overpayment and wrong-party allocations rejected | Customer/vendor screens and API use the same Account services. Supplier withholding is included in settlement. |
| G03 Stock conservation | Locked invoice/GRN/delivery/POS posting; shortages rejected; receipt/invoice and delivery/invoice double movement prevented | Partial receipt and retry tests pass. All valuation/COGS policies and MySQL concurrency are not certified. |
| G04 Sales fulfillment | Existing sales orders convert to existing delivery/invoice models, support partial delivery, constrain reserved stock, and reverse fulfillment on cancellation | Reservation uses existing order items; no competing reservation module. |
| G05 Procurement | Real existing purchase_orders/items connected to PR/RFQ, receipt, bill, refreshed matching and supplier payment hold | Supplier portal accepts issued POs and uploads private attachments. |
| G06 Access/reversals | Added company and permission checks to affected controllers; locked, scoped reversals restore GL balances; finalized records protected | Full branch/department/amount RBAC across every legacy package remains open. |
| G07 DB relationships | Applied restrictive financial/stock/document/PO foreign keys, additive workflow columns, encrypted exception retention and quantity precision expansion | 37 foreign-key columns across 21 tables, 294 transactional tables. One historical orphan delivery item was encrypted and retained before removal from the active orphan set. |
| G08 Audit/signatures | Audit payload hash covers before/after values; model immutability; version 2 document signatures bind source content, signer and time, with encrypted signed payload | Legacy registry entries are identified as legacy; seven-year WORM/archive operations and offline PKI are not implemented. |
| G09 API | Existing master controllers expose company-scoped customer/supplier/product/invoice writes; operational order, posting, payment, withholding, supplier portal and controls endpoints | API contract in SRS_API.md. Bulk imports/exports and complete OpenAPI remain open. |
| G10 Accounting | Accounting period locks, configured stock variance journals, gross/net payroll split, customer credit hold, withholding settlement/remittance and report controls | Account mappings must be configured in existing reports controls before posting affected transactions. Full valuation, consolidated reporting and all payroll rules remain open. |
| G11 Security/operations | TOTP enrollment/challenge/recovery with replay prevention; encrypted gateway credentials; disabled-account enforcement; encrypted backup and isolated restore | Malware scanner, offsite scheduler/PITR, native provider certification, offline POS and production UAT remain open. |
| Dedicated ERP | Active SaaS billing/subscription/coupon/plan assignment routes, pricing page and upgrade UI removed; public self-registration disabled | Existing module access is perpetual. Legacy files/database history remain for compatibility; no active subscription lifecycle. |
| Customer portal | Revision feedback reopens the existing quotation as draft; sales revises/resends through its existing workflow | Customer actions are locked and available only for a sent, owned quotation. |

## Verification artifacts

- Final focused result: **31 tests, 205 assertions passed**. Root TypeScript completed without diagnostics; Vite production build passed and assets were published to public/build. Local login and the current app JavaScript asset returned HTTP 200. Active subscription/plan billing routes: zero. Public registration is disabled.
- Latest restored snapshot: `mutamark_backup_2026-10-14_14-08-27_lKLhPjbN.sql.enc`; all 294 table counts matched the source and the isolated temporary schema was removed. Migration 077 is included. Schema scan: 37 foreign-key columns, 21 tables with foreign keys; all 13 aggregate integrity checks zero. App maintenance mode is off.
- PHP syntax scan passed for 133 changed/relevant files, with subsequent touched controllers/models/routes/migration checked individually. This is syntax validation, not a complete semantic review of every legacy package.
- Focused isolated regression suite: see scratch/mutamark-tests.log for the final result. SQLite isolation is asserted before test schema operations.
- Root TypeScript: scratch/mutamark-types.log; production frontend: scratch/mutamark-build.log. Package JSX is bundled by Vite; root tsc does not certify every legacy package type.
- Read-only live schema and integrity scan: scratch/srs-rescan-schema.json; foundational checks: scratch/srs-rescan-db-audit.json. Aggregate checks are not a full ledger reconciliation.
- Authenticated encrypted full backup and isolated restore: scratch/srs-restore-rehearsal.json; all table row counts compared to the source. No business database restore was performed.
- Historical starter core-suite failures remain separate; the original auth/registration starter assertions are not evidence of ERP SRS compliance.
- No authenticated visual UAT, MySQL concurrency/load run, production provider/hardware certification or independent penetration test was performed.

## Remaining SRS acceptance gaps

Branch/department/amount permissions; fiscal/branch numbering; catalog variants/kits/batch expiry and complete valuation; POS shifts, held/offline sales and hardware; notification jobs/inbox/push; full portal histories and tokenized external access; all document-template generation/send/archive pipelines; seven-year archive/WORM and malware integration; full report/export parity; offsite incremental/PITR and OS scheduling; production accessibility, performance, provider and management acceptance. Historical gateway exceptions require finance evidence. These are unfinished requirements, not declared complete or replaced with duplicate modules.

## Historical discovery evidence (before the changes above)

The following findings and 35-module matrix record the starting scan. References to missing capabilities in this historical section must be read with the implementation status above. The original scan was read-only; the subsequently authorized implementation changed application code and the database as documented above.

## Scope and evidence

- Re-extracted the 23-page MUTAMARK_ERP_SRS_WOLINET.pdf and visually checked its acceptance page. Compared functional modules 3.1-3.35, interfaces/NFRs, data retention and Appendix B controls against current source.
- Inventoried 5,311 files under app, resources/js/pages, routes, database/migrations and packages/workdo; inspected 1,533 registered routes. Inventory includes package resources, not just executable code. This is a repository-wide discovery scan plus manual tracing of important workflows, not a line-by-line verification of every file.
- Queried live database metadata and aggregate integrity checks without changing business records. Artifacts: scratch/srs-rescan-schema.json and scratch/srs-rescan-db-audit.json. No real payment, stock or approval mutations were executed for this review.
- Reran the isolated SRS regression suite: **18 tests, 109 assertions passed**. Artifacts: scratch/srs-rescan-tests.log and .xml. These tests cover selected hardened workflows, not all modules or MySQL concurrency.
- The last recorded original core-suite run has **10 failures in 25 tests**. It was not rerun in this rescan; see scratch/core-tests.log. Earlier TypeScript/build/syntax results remain historical evidence, not new checks in this review.
- No authenticated browser workflow, hardware/provider integration, vulnerability assessment or concurrent-user load test was performed. Deployment-only requirements remain unverified.

Status language: **confirmed defect** has a concrete source/data finding; **missing/disconnected** means the scanned application lacks a connected implementation; **partial** means usable foundations exist but mandatory work remains; **unverified** means acceptance evidence is missing. A database table or menu alone is not a complete feature.

## Highest-priority findings

### G01 - Existing invoice overpayment needs reconciliation [P1, data]

Live invoice **INV-2026-0001 (ID 1)** has total **450,000.00**, paid **1,700,000.00**, balance **0.00**. Paid exceeds total by **1,250,000.00**, and total minus paid does not equal balance. This is one affected invoice, counted by both the overpayment and balance-arithmetic checks. It directly fails Appendix B payment control and acceptance criterion 7.

Evidence: scratch/srs-rescan-schema.json, affected_sales_invoices. The scan does not establish which historical operation caused it or whether an authorized advance exists elsewhere. Trace receipts, allocations, bank movements and journals before correcting anything; changing the invoice header alone would hide the discrepancy. No correction was made.

### G02 - Ordinary Account payment clearance remains replayable [P1, backend/DB]

The unified gateway settlement was hardened, but the existing customer/vendor payment screens use different controllers. Calling updateStatus with cleared again invokes financial posting and adds allocations to invoice paid_amount again. There is no prior-state/idempotency guard, enclosing transaction or row lock in these controller methods. Their allocation request validation checks existence and positive amounts, but not company/customer/vendor ownership, allocation totals or current invoice balances. Supplier clearing also does not consult three-way matching.

Evidence: [CustomerPaymentController.php:181](C:/wamp64/www/mutadash/packages/workdo/Account/src/Http/Controllers/CustomerPaymentController.php:181), [VendorPaymentController.php:179](C:/wamp64/www/mutadash/packages/workdo/Account/src/Http/Controllers/VendorPaymentController.php:179), and their StoreCustomerPaymentRequest/StoreVendorPaymentRequest. Reuse the Account receipt/payment/journal/bank services under one locked posting boundary for every entry point. Test retries, concurrent clearance, wrong-party allocations, overpayment and failed posting rollback.

### G03 - Stock posting is not conserved across sales and purchase paths [P1, backend/DB]

Delivery dispatch still uses max(0, stock - shipped), recording the full shipment even when stock is short. Sales-invoice posting independently decrements warehouse stock without an availability check. A linked delivery and invoice can therefore deduct the same sale twice. GRN posting increases stock, and purchase-invoice posting independently increases it again. GRN state is checked before the transaction without a header lock; sales-invoice posting dispatches listeners and updates status without one atomic transaction. The improved transfer/count paths do not fix these separate paths.

Evidence: [DeliveryNoteService.php:180](C:/wamp64/www/mutadash/app/Services/DeliveryNoteService.php:180), [SalesInvoiceController.php:341](C:/wamp64/www/mutadash/app/Http/Controllers/SalesInvoiceController.php:341), [GoodsReceivedNoteController.php:265](C:/wamp64/www/mutadash/app/Http/Controllers/GoodsReceivedNoteController.php:265), ProductService PostSalesInvoiceListener and PostPurchaseInvoiceListener. Establish exactly one inventory movement per commercial event and link delivery/receipt fulfillment to invoicing. Test both posting orders, partial quantities, shortages, retries and rollback.

### G04 - Sales-order delivery conversion is broken; reservations are only flags [P1, frontend/backend]

convertToDeliveryNote calls DeliveryNote::generateDeliveryNumber(), which is not defined on that model; the generator exists on DeliveryNoteService. It also supplies ordered_quantity/delivered_quantity instead of the delivery item model's quantity_ordered/quantity_shipped fields and bypasses the service dispatch workflow. toggleStockReservation only toggles a boolean/status; it does not reserve available product quantities or constrain other issues. The frontend can therefore promise reservation/fulfillment without the required stock control.

Evidence: [SalesOrderController.php:255](C:/wamp64/www/mutadash/app/Http/Controllers/SalesOrderController.php:255), [SalesOrderController.php:269](C:/wamp64/www/mutadash/app/Http/Controllers/SalesOrderController.php:269), DeliveryNote/DeliveryNoteItem and DeliveryNoteService. Wire the existing sales-order screens to the existing delivery service and a shared reservation ledger; do not create another delivery module.

### G05 - Distinct PO and enforced three-way payment lifecycle are disconnected [P1, all layers]

purchase_orders exists in the live schema, but no PurchaseOrder model/controller or operational PO routes were found in app/routes. PR conversion and RFQ award create PurchaseInvoice as a combined PO/bill. The supplier portal also labels purchase invoices as purchase orders. Matching audit firstOrCreate does not refresh an existing clearance when variances change; authorizeDisbursement can mark a record cleared without re-auditing, and ordinary supplier payment clearance ignores it. This does not enforce PR -> RFQ -> PO -> GRN -> invoice -> matched payment.

RFQ award additionally treats a Vendor-table ID as a User ID first and falls back to the first vendor user without company scope, risking a purchase invoice assigned to the wrong supplier.

Evidence: [PurchaseRequisitionService.php:214](C:/wamp64/www/mutadash/app/Services/PurchaseRequisitionService.php:214), [RfqService.php:250](C:/wamp64/www/mutadash/app/Services/RfqService.php:250), [ThreeWayMatchService.php:117](C:/wamp64/www/mutadash/app/Services/ThreeWayMatchService.php:117), :151, PortalController::supplierPortal and VendorPaymentController::updateStatus. Extend the existing PR/RFQ/GRN/matching and purchase-order tables, preserving distinct document identities and supplier.user_id mapping.

### G06 - Company/permission controls are still inconsistent [P1, backend/API]

Examples remaining outside the new CompanyAccess-protected paths: delivery mutations accept route-bound documents without company checks; GRN posting loads by arbitrary ID; asset transfers/disposals and service-ticket updates use unscoped IDs; sales-order mutations lack domain permission/company enforcement; three-way clearance/dispute actions do not scope authorization IDs; audit and verification ledgers query all companies. Authentication and PlanModuleCheck do not substitute for these checks.

SalesInvoiceController::destroy blocks only status posted, permitting partial/paid/overdue finalized invoices to enter deletion, and does not scope the invoice to the current company. This violates finalized-document integrity.

The journal reversal endpoint also lacks a domain permission check, and FinancialControlService::createReversalJournal loads the original journal by ID without company scope or a row lock. It copies the reversal into the current user's company. A reversal feature existing in source therefore does not establish authorized or safe accounting correction.

Evidence: DeliveryNoteController::update/dispatchNote/confirmDelivery/cancelNote/destroy; GoodsReceivedNoteController::post; FixedAssetController::storeTransfer/storeDisposal/updateTicketStatus; WarrantyServicingController::updateTicket; SalesOrderController; ThreeWayMatchController; [SalesInvoiceController.php:272](C:/wamp64/www/mutadash/app/Http/Controllers/SalesInvoiceController.php:272); FinancialAuditTrailController::index and DocumentVerificationController::index. Add server-side permissions, scope every identity and enforce state within the transaction. Audit all analogous package mutations rather than relying on hidden buttons.

### G07 - Database has no foreign-key constraints [P1, DB]

The live database has **293 InnoDB tables and zero foreign-key constraints**. The storage upgrade did not restore relationships previously ignored by MyISAM. This leaves referential integrity dependent on application code, including unsafe deletion paths. Current invoice-line, stock-parent, document-version and journal-item orphan checks returned zero, but future writes can still create invalid links.

Live schema also lacks a commercial branch assignment on invoices, sales orders and warehouses; sales/purchase invoice item quantities are integer while warehouse quantities are decimal; formal invoices have no currency field. Customer master lacks credit_limit. These prevent consistent branch/amount/credit and fractional/FX workflows.

Evidence: scratch/srs-rescan-schema.json. Build a relationship/ownership map, preflight historical data and add reviewed FKs/indexes with appropriate restrict/retention behavior. Do not apply blanket cascading deletes to financial history.

### G08 - Signatures and audit hashes do not bind complete immutable content [P1, backend/DB]

Approval signatures hash request metadata but not a canonical document version or line-item/file content digest. Public verification findByHash performs a stored-hash lookup, not independent recomputation of signed content. FinancialAuditLog hashes metadata but excludes old_values/new_values and has no model update/delete protection. Seven-year immutable retention and broad administrative/denied-action coverage are not implemented. The presence of QR codes and a hash-chain UI does not establish the SRS authenticity/immutability requirements.

Evidence: ApprovalRequest::generateSignature, [DocumentVerificationService.php:78](C:/wamp64/www/mutadash/app/Services/DocumentVerificationService.php:78), FinancialAuditLog::computeHash/record. Bind a persisted immutable document version and signer attributes; verify content integrity and revocation state; protect audit persistence and retention.

### G09 - Core REST coverage is partial and the payment scope column is wrong [P2, API]

The core resource controller provides explicit read endpoints, plus separate document/proforma/count/approval writes. It lacks the full operational create/update/import/export contracts expected for an external ERP integration. Its default company filter is created_by for all resources; PaymentTransaction uses creator_id for company and created_by for actor. Consequently the payments API omits staff-created company payments and does not use the model's actual tenant key.

Evidence: Api/ErpResourceController::resources/query and PaymentTransaction::markAsCompleted, together with PaymentGatewayController::generatePaymentLink. Use resource-specific ownership keys and test each list/detail with two companies and staff actors. Keep Sanctum; it already supplies token authentication and does not need replacing merely to add these contracts.

### G10 - Accounting coverage remains incomplete [P1/P2, all layers]

StockAdjustmentController changes warehouse quantities but creates no inventory adjustment journal. Payroll accounting exists and is called by Account PaySalaryListener, but createPayrollJournal debits/credits net pay only; it does not recognize gross salary and deduction liabilities as required. paySalary lacks a prior-paid guard/transaction. Financial period locking/year closing and bank statement import were not found in the active Account flow; bank reconciliation currently supports manually marking transactions reconciled.

Evidence: StockAdjustmentController::postAdjustment; Account JournalService::createPayrollJournal at :1024; Hrm PayrollController::paySalary; Account BankTransactionController::markReconciled and routes. Legacy DoubleEntry contains a separate journal-delete path and old Modules namespaces; it must not be treated as a validated replacement for Account journals. Confirm what is enabled and standardize on existing Account services with reversal and period controls.

### G11 - Security and operational capabilities remain unconnected [P2, frontend/backend/operations]

No working core 2FA enrollment/challenge/recovery flow was found; legacy packages referencing middleware named 2fa do not establish one. Gateway credentials use plain array storage casts with no application-level encryption shown; infrastructure encryption at rest is unverified. Document upload MIME/extension restrictions exist, but malware scanning is not connected. The API delete-account path hard-deletes the user without a retention workflow, while the web profile destroy route points to a missing method.

Backups now encrypt/authenticate and an isolated restore was previously proven, but only full backups are implemented. Offsite storage needs configuration; daily application scheduling needs the OS scheduler; incremental/PITR, automatic retention and documented production recovery targets remain open. PWA caches public static assets safely but has no transaction outbox/synchronization for POS/inventory. POS cash shifts, variance balancing and hardware protocols remain incomplete.

## Current coverage across all 35 modules

This table lists remaining work, not features to rebuild. All rows are partial or unverified against full acceptance; an absence of complete certification is not an assertion that the entire module is missing.

| SRS | Existing foundation to reuse | Remaining frontend/workflow | Remaining backend/API/DB/acceptance |
|---|---|---|---|
| 3.1 Administration | Settings, currencies, warehouses, locked sequence service | Financial years/periods, numbering format/branch/year and backup policy management | Number service uses global company_id=0/calendar year; several generators remain separate; configuration audit/period locks missing |
| 3.2 Company/branch | Company users, Hrm branches, warehouses | Commercial branch assignment and consolidated views | Commercial branch tenant policy and branch financial reporting not connected; G06/G07 |
| 3.3 RBAC/security | Spatie roles, login history, disabled-user/API checks | 2FA and concurrent-session management; warehouse/amount permissions | Uniform domain/company/branch/department/amount enforcement and secure credential storage; G06/G11 |
| 3.4 CRM | Account Customer, Lead, helpdesk | Customer credit/category/default-discount and communication timeline | credit_limit/enforcement/override and complete history; reuse existing customer master |
| 3.5 Suppliers | Account Vendor, RFQ, aging/payment reports | Qualification documents and evaluation/rating, PO confirmation | Correct Vendor/User identity mapping and protected ledger/payment lifecycle; G02/G05 |
| 3.6 Catalog | ProductService items/categories/units, serial registry | Attributes/variants, kits, wholesale/minimum price, margin, batch/expiry controls | Base item schema lacks these fields; no connected variant/batch/costing engine found |
| 3.7 Inventory | WarehouseStock, transfers, stock adjustments/scanner | Serial scan deduplication, movement/valuation, damage/expiry/project issues | Shared issue/receipt/reservation ledger, valuation, adjustment journals, negative-stock control; G03/G04/G10 |
| 3.8 Procurement | PR/RFQ/GRN/matching screens; purchase_orders table | Operational distinct PO, partial receipts, authorization/commitments | PO model/routes disconnected; duplicate receipts, supplier mapping and mandatory payment matching; G03/G05 |
| 3.9 Sales | Proposals, new proformas, orders, deliveries, invoices, returns | Working order-delivery conversion and real partial/reserved fulfillment | Credit control, exactly-once stock/accounting, fractional/FX support and immutable finalized documents; G02-G04/G06 |
| 3.10 POS | Existing POS/counters/barcode/split payments/returns | Held-sale retrieval, shift opening/closing, cash variance, customer display | Offline transaction sync, ESC/POS/cash drawer/scale integration; fiscal Z page is not register balancing |
| 3.11 Accounting | Account COA/journals/reports/services; legacy DoubleEntry | Year close, period administration and statement reconciliation import | Complete posting/reversal/period controls and transaction-to-report reconciliation; G02/G03/G10 |
| 3.12 Tax | Existing tax assignment, Tanzania payroll/fiscal screens; WHT table | WHT record/certificate/remittance/liability interface | withholding_taxes has no domain model/controller/routes found; fiscal service uses placeholder identity and random acknowledgment, not provider submission |
| 3.13 Expense/income | Account Expense/Revenue/categories and approval engine | Complete request -> approval -> voucher -> payment tracking | Enforce each step/actor and integrate posting/reversal; acceptance tests outstanding |
| 3.14 Cash/bank | Account bank accounts/transactions/transfers | CSV/Excel/MT940 statement import, matching, cash floats | Reconciliation/settlement consistency and safe payment clearing; G02/G10 |
| 3.15 HR | Hrm employees/branches/departments/employment operations | Organizational chart, contract renewals and incentive workflows | Alerts and full employment/history acceptance; no need for another employee module |
| 3.16 Attendance/leave | Hrm attendance/leave/holiday and payroll consumption | Correction-request approvals and clear balance/exception UX | Cross-employee authorization and attendance-to-payroll reconciliation tests |
| 3.17 Payroll | Hrm payroll/payslips, statutory service/reports, Account salary listener | Salary history/variance and approval/paid-state feedback | Gross/deduction-liability accounting, safe retries, statutory reconciliation; G10 |
| 3.18 Projects | Taskly projects/tasks/milestones/payments/reports; timesheets | Material/labor/expense profitability and customer comments | Choose existing time/cost source, connect stock issues and revenue recognition; verify report cost basis |
| 3.19 Tasks | Taskly configurable stages/comments/files/calendar/subtasks | Map Draft/Assigned/In Progress/Review/Completed/Closed; time/progress parity | Enforce required stage transitions/permissions and completion/time reporting; configurable stages are already present |
| 3.20 Assets | FixedAsset registration/depreciation/transfer/disposal screens | Physical verification and complete history | Tenant/actor checks, authorized disposal/reversal and tested depreciation journal linkage; G06 |
| 3.21 Warranty/service | WarrantyServicing and existing MobileServiceManagement | Expiry alerts, vendor-repair lifecycle and paid service invoice linkage | warranty_expiry_alerts disconnected; scope ticket updates and validate serial/invoice ownership; G06 |
| 3.22 DMS | New private Document repository/version/download/archive; FileSharing/media | Entity attachment controls, archive/restore browser and retention management | API linking exists for supported entities, but full contract linkage/malware/90-day archive pipeline absent; do not rebuild version storage |
| 3.23 Signatures | Approval signatures, QR/verification ledger/pages | Version/signer position/revocation and number+code verification UX | Canonical immutable content binding and independent verification; G08 |
| 3.24 Approvals | Assigned actor/state/tier/delegation service and UI | Parallel paths, department routing and document-specific delegation selection | Extend policy/routing without replacing sequential engine; delegation creation currently requires manage-account; full downstream posting tests |
| 3.25 Templates | Existing template builder/service and print pages; proforma reuses quotation print | All required templates selectable in business flow; send/archive status | Full Prepare -> Review -> Sign -> PDF -> Send -> Archive integration and stored generated version |
| 3.26 Notifications | Email templates, SMS/WhatsApp listeners, messenger | Recipient/read notification center and subscriptions/preferences | Automated overdue/stock/expiry/contract/approval/task jobs and PWA push not found; templates are not a delivery inbox |
| 3.27 Customer portal | Scoped customer quotations/invoices/deliveries/payments/tickets | Complete order/payment history, document downloads and revision flow | Portal token table disconnected; quotation Request Revision currently maps to rejection, not a dedicated revision lifecycle |
| 3.28 Supplier portal | Scoped RFQs/bids and purchase-bill listing | PO acceptance, invoice/delivery uploads and company profile editing | Real PO workflow, upload/profile endpoints and full payment/history linkage; G05 |
| 3.29 Barcode/serial | POS barcode, serial registry, count scanner, document QR | Serial/batch capture at each receipt/issue/delivery; label-format parity | Unique serial state/movement validation and duplicate-scan prevention; do not create another scanner module |
| 3.30 Reporting | Account/Hrm/Pos/Taskly reports and print views | Branch/expiry/damage/price variance, uniform date/PDF/Excel/CSV parity | Reconcile each report to journals/movements and scope it correctly; G01/G03/G06 |
| 3.31 Dashboard | Core and package dashboards/KPIs | Role-aware consolidated financial/operational/HR/project view | Permission/company scopes, bounded aggregates and source reconciliation; production concurrency/latency unverified |
| 3.32 REST/integration | Sanctum, scoped core reads, selected writes, package APIs, throttling/logging | Token/integration admin and complete contract/import/export documentation | Resource-specific tenant keys, operational writes/imports/exports and integration event contracts; G09 |
| 3.33 Payments | Hardened generic signed checkout callback; existing Stripe/PayPal packages | Finance verification status and real provider configuration UX | Native provider verification/adapters, safe Account manual clearing, FX/advances/fees and successful-settlement reconciliation; G02 |
| 3.34 Backups/DR | Encrypted full snapshots, verify command, daily app schedule, isolated rehearsal | Retention/history/integrity/restore status UI | Offsite configuration, OS scheduling, incremental/PITR and production RTO/RPO/runbook; G11 |
| 3.35 Audit | Financial audit record/filter/export and approval actions | Broad sensitive/admin/denied action coverage | Hash before/after content, enforce immutability, seven-year retention and scope access; G06/G08 |

## Database scan interpretation

The narrow srs:audit command passes: no missing foundational tables/columns, pending migrations, non-InnoDB tables, duplicate stock keys, negative stock rows or orphan payment invoice links. It checks a small declared set; it does not establish full SRS completeness.

The broader scan adds relationship metadata, focused column/index review and 13 aggregate checks. Six sampled orphan checks, purchase balance/overpayment checks, posted journal header/line checks and duplicate completed provider-reference checks returned zero. Sales overpayment/balance checks exposed G01. Zero currently negative stock does not prove stock-issue safety, and balanced journal headers do not prove every business event was posted.

Disconnected tables include purchase_orders, portal_access_tokens, withholding_taxes and warranty_expiry_alerts. stocktake_sessions/items also have no connected workflow, but the existing stock-adjustment cycle count is already operational: extend that source of truth instead of activating a competing stocktake engine.

## Existing fixes that remain present

The rescan found the implemented DMS/version screens and private file rules, proforma routes/service/print adaptation, transfer/count locks, approval delegation/actor/state checks, disabled-account API guard, signed generic payment callbacks, masked gateway serialization, portal identity checks, safe static-only service worker and encrypted backup/restore tooling. These should not be reported as wholly missing or rebuilt. The focused regression suite still passes.

