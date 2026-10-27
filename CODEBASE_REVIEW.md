# Codebase review — 14 October 2026

## Scope and checks

Repository-wide source inventory and PHP parse check, core React TypeScript check, production frontend build including discovered package entrypoints, and targeted manual review of routes, middleware, authentication, payments, approvals, portals, inventory, document templates, financial controls and package posting listeners. This is not a claim that every business rule in every add-on was manually verified. Third-party vendor/node_modules source was excluded from manual review. No application code was changed.

- PHP parse check: **3,944 files, zero parse errors**, covering app, bootstrap, config, routes, database, packages and tests. This does not prove runtime correctness.
- `node node_modules/typescript/bin/tsc --noEmit --pretty false`: **passed**. The configured include covers resources/js; it does not comprehensively type-check package TSX files.
- Production Vite build: **passed**, 4,302 modules transformed. Output was directed to scratch/review-build. The first attempt was blocked by sandbox subprocess restrictions; the permitted retry succeeded.
- PHPUnit: **25 tests; 1 passed, 23 errors, 1 failure; 2 assertions**. Run with DB_CONNECTION=sqlite and DB_DATABASE=:memory: to isolate it from the application database. Feature-test setup errors report MySQL-specific ALTER TABLE ... MODIFY syntax in the vehicles migration; these errors prevent those tests from evaluating their intended behavior. The basic homepage feature test also failed.
- `php artisan route:list --json`: **passed**, application and package routes registered successfully in the isolated environment.
- This directory is not a Git working tree, so there is no Git baseline or diff available.

## Findings

P1 means high priority; P2 means a functional or configuration defect that should be addressed. These findings are supported by source tracing; payment, restore, and inventory mutation requests were not executed against business data.

### 1. [P1] Public checkout settles payments without payment verification

**Location:** app/Http/Controllers/PaymentGatewayController.php:461–485.

Submitting the public checkout form directly calls markAsCompleted. A missing reference is replaced with a generated mock transaction ID, and no provider payment request, confirmation lookup, or finance approval occurs. This also happens for gateways configured as live. Anyone holding a checkout link can mark its invoice paid without transferring money. Keep submissions pending and settle only after trusted provider verification or an authorized manual approval.

### 2. [P1] Payment webhooks accept unsigned, unverified payloads

**Location:** app/Http/Controllers/PaymentGatewayController.php:509–529; routes/web.php public payment webhook route.

The public webhook accepts a transaction reference or checkout token and completes the transaction. It never validates the configured webhook secret, the route gateway against the transaction gateway, a successful provider status, amount, or currency. A caller can submit a known or predictable TXN reference and create a settlement. Validate the provider-specific signature and settlement details before changing financial state.

### 3. [P1] Repeated settlement credits invoices repeatedly and is not atomic

**Location:** app/Models/PaymentTransaction.php:90–148.

markAsCompleted has no already-completed guard, transaction boundary, or row lock. Every call inserts another receipt and adds the full amount to invoice.paid_amount. Webhook retries or repeated checkout submissions therefore create duplicate credits; concurrent calls also race. Exceptions after the receipt insert can leave partial state. Use one database transaction, lock the payment and invoice, and enforce an idempotency key with a database constraint.

### 4. [P1] Backup operations lack administrator authorization

**Location:** app/Http/Controllers/BackupController.php:26, 140, 157; routes/web.php:357–361.

The backup routes require authentication and verification but no administrative role. The controller performs no permission checks. Any verified user can list, create, download, restore, or delete full database snapshots. Download exposes all companies' data; restore replaces shared database state. Require an explicit backup-administration permission on every action.

### 5. [P1] API profile editing accepts another user's ID

**Location:** app/Http/Controllers/Api/AuthApiController.php:129–137.

editProfile uses the supplied user_id to select a user without checking ownership, role or company. Any Sanctum-authenticated user can modify another user's name, email, phone or avatar by choosing their ID. Self-service editing must use request->user(); administrative editing needs a separate authorized, scoped action.

### 6. [P1] Approval actions do not enforce the assigned approver or workflow state

**Location:** app/Http/Controllers/ApprovalWorkflowController.php:148–237.

The action endpoint retrieves an arbitrary approval request and checks only self-approval segregation for approve/sign. It never checks current_approver_id, required role, company, or allowed transitions. A different low-privilege user can approve repeatedly through tiers. The requester can also use issue, which bypasses the approve/sign segregation check and calls syncDocumentStatus. Enforce actor authorization and legal transitions on the server for each action.

### 7. [P1] Gateway management exposes secrets and permits unauthorized changes

**Location:** app/Http/Controllers/PaymentGatewayController.php:40–66, 198–235; app/Models/PaymentGateway.php.

The index serializes the full model, including raw credentials and webhook_secret, and then adds masked_credentials without removing the originals. Models have no hidden secret attributes. The management methods have no role checks or company-scoped lookup, so verified users can read secrets or alter payment configuration. Restrict management and serialize an explicit safe field list, excluding original secrets.

### 8. [P1] Self-service portals do not enforce customer/vendor ownership

**Location:** app/Http/Controllers/PortalController.php:27–42, 103–119, 187–207, supplierSubmitBid.

customer_id and vendor_id choose arbitrary accounts. The pages expose all customers/vendors, and proposal acceptance/rejection loads arbitrary IDs without checking ownership. Supplier bid submission trusts the supplied vendor_id. Customers can inspect another customer's records or act on their quotations; suppliers can impersonate another vendor. Bind portal identities to the authenticated user's authorized account and scope every query and mutation.

### 9. [P1] New in-transit transfers never deduct source stock

**Location:** app/Http/Controllers/TransferController.php:126, 178–180; app/Models/Transfer.php:109–113.

store saves the transfer as in_transit (also the default), then calls dispatchOrder. dispatchOrder immediately refuses an in_transit transfer, so source stock is never deducted. Receiving it still increases destination stock. Create in draft and perform the transition and stock movement atomically. Example: transferring 5 from a source with 10 currently leaves 10 at source and adds 5 at destination.

### 10. [P1] Transfer receipt accepts invalid states and unbounded quantities

**Location:** app/Models/Transfer.php:150–180; app/Http/Controllers/TransferController.php:251–257.

receiveOrder rejects only received/cancelled, allowing draft and completed transfers to add destination stock. A completed transfer was already credited by the immediate-transfer path, so receiving it credits stock twice. Inspections are validated only as an array; values can be negative or exceed dispatched quantities. Require the in_transit state, validate each item's received quantity, and lock the transfer during receipt.

### 11. [P1] Insufficient stock is silently clamped instead of rejected

**Location:** app/Http/Requests/StoreTransferRequest.php:48–54; app/Http/Controllers/TransferController.php:389–408; app/Services/DeliveryNoteService.php:180–197.

Multi-line transfer quantities have no availability check, while immediate transfer subtracts with max(0, stock - quantity) and credits the entire requested quantity at destination. Delivery dispatch likewise clamps insufficient source stock to zero and records the full shipped quantity. This creates stock or reports shipments beyond available inventory. Validate aggregated quantities per product and warehouse inside a locked transaction; fail instead of clamping. The existing FinancialControlService stock guard is not invoked by these paths.

### 12. [P1] Approval marks purchase invoices posted without posting effects

**Location:** app/Http/Controllers/ApprovalWorkflowController.php:381–391; app/Http/Controllers/PurchaseInvoiceController.php:384–397.

syncDocumentStatus sets status=posted directly. Normal posting dispatches PostPurchaseInvoice, whose ProductService listener increases warehouse stock and Account listener creates a purchase journal. Workflow-approved invoices skip those effects, and the normal posting endpoint subsequently refuses them because they are no longer draft. Keep approval separate from posting or invoke a shared atomic posting service exactly once.

### 13. [P1] API authentication bypasses the disabled-account check

**Location:** app/Http/Controllers/Api/AuthApiController.php:19–39; app/Http/Requests/Auth/LoginRequest.php:77–86; routes/api.php.

Web login rejects users with is_enable_login=false, but API login checks only the password and issues a Sanctum token. Administratively disabled users can therefore authenticate through the API. There is also no explicit login throttle on this API route or in the method. Share the account eligibility checks and add a dedicated credential-attempt rate limit.

### 14. [P2] Customer portal eager-loads a nonexistent invoice relationship

**Location:** app/Http/Controllers/PortalController.php:49–51; app/Models/SalesInvoice.php.

The invoice query calls with(['items', 'payments']), but SalesInvoice defines paymentAllocations, not payments. Loading a customer with invoices raises RelationNotFoundException. Use the implemented allocation relationship (and its payment relation as needed), or define a proper payments relationship.

### 15. [P2] Customer payment submission omits a required gateway key

**Location:** app/Http/Controllers/PortalController.php:138–149; database/migrations/2026_10_10_160000_create_unified_payment_gateways_tables.php:43.

The portal inserts payment_method, payer_name and notes, which are absent from PaymentTransaction's fillable attributes, and omits gateway_key, a non-nullable database column with no default. Submitting a reference fails on the declared schema. Map validated inputs to supported model fields, resolve a valid gateway, and include the required customer/ownership data.

### 16. [P1] Default feature-test configuration can reset the application database

**Location:** phpunit.xml:25–26; tests/Feature/ProfileTest.php:11 and authentication feature tests.

Tests use RefreshDatabase but the isolated SQLite database settings are commented out. With no separate testing environment or shell override, Laravel uses the application's configured database and RefreshDatabase can run migrate:fresh against it. Configure a dedicated test database explicitly and add a safeguard against using the normal database. The review invocation overrode these values with in-memory SQLite.

### 17. [P2] A MySQL-specific migration blocks the isolated SQLite test suite

**Location:** database/migrations/2026_10_09_181000_fix_vehicles_table_columns.php:13–21.

The migration unconditionally executes MySQL ALTER TABLE ... MODIFY statements whenever vehicles exists. SQLite rejects the first statement during RefreshDatabase setup, as observed in the isolated test run. Use Laravel schema changes supported by the chosen test driver, or use an explicitly isolated MySQL test database and document that requirement. Fixing this setup blocker is necessary before the feature suite can provide meaningful application validation.

## Recommended order

1. Stop unverified payment settlement; enforce webhook authentication and idempotency.
2. Add authorization and ownership enforcement for backups, approval actions, profile editing, gateways and portals; remove gateway secrets from serialized responses.
3. Fix transfer transitions and stock validation; unify financial posting effects.
4. Repair portal model/schema mismatches and isolate the test database permanently.
5. Add focused regression coverage for unauthorized requests, duplicate callbacks, approval transitions, stock conservation, and financial posting. Existing tests are starter authentication/profile tests, with no discovered add-on test files.

## Limitations

No live payment provider calls, backup restores, business-data mutation tests, browser walkthroughs, external dependency vulnerability audit, or legal/tax-rule validation were performed. Successful syntax/type/build checks do not establish accounting correctness or security. Findings above are sufficient to require remediation before relying on these flows in production.
