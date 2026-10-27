<?php

namespace Tests\Srs;

use App\Models\Document;
use App\Models\ProformaInvoice;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\DocumentNumberService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // This suite must never run schema operations against the business database.
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2025_08_12_105132_create_permission_tables.php',
            '2025_08_12_105136_create_warehouses_table.php',
            '2025_09_26_102340_create_sales_invoices_table.php',
            '2025_09_26_102341_create_sales_invoice_items_table.php',
            '2025_09_26_102342_create_sales_invoice_item_taxes_table.php',
            '2025_09_26_102328_create_purchase_invoices_table.php',
            '2025_09_26_102334_create_purchase_invoice_items_table.php',
            '2025_09_26_102335_create_purchase_invoice_item_taxes_table.php',
            '2025_11_10_120000_create_sales_proposals_table.php',
            '2025_11_10_120001_create_sales_proposal_items_table.php',
            '2025_09_13_000002_create_transfers_table.php',
            '2026_10_09_000001_create_document_verifications_table.php',
            '2026_10_10_170000_extend_warehouses_wms_features.php',
            '2026_10_10_130000_create_multi_tier_approval_tables.php',
            '2026_10_10_160000_create_unified_payment_gateways_tables.php',
            '2026_10_14_050000_add_srs_missing_tables.php',
            '2026_10_14_060000_add_document_sequences_and_retention.php',
            '2026_10_11_000001_create_sales_orders_tables.php',
            '2026_10_10_210000_create_delivery_notes_tables.php',
            '2026_10_09_000003_create_goods_received_notes_table.php',
            '2026_10_09_000004_create_financial_audit_logs_table.php',
            '2026_10_10_220000_create_purchase_requisitions_tables.php',
            '2026_10_14_073000_add_mutamark_two_factor_authentication.php',
            '2025_08_18_104001_create_settings_table.php',
            '2025_08_28_083657_create_add_ons_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        Schema::create('product_service_items', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('sku')->nullable();
            $table->string('unit')->nullable(); $table->decimal('sale_price', 15, 2)->default(0);
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->boolean('is_active')->default(true); $table->unsignedBigInteger('created_by');
        });
        Schema::table('users', function (Blueprint $table) { $table->boolean('active_status')->default(false); $table->timestamp('last_seen_at')->nullable(); });
        (require glob(base_path('packages/workdo/ProductService/src/Database/Migrations/*create_warehouse_stocks_table.php'))[0])->up();
        (require database_path('migrations/2026_10_14_061000_enforce_inventory_stock_uniqueness.php'))->up();
        Schema::create('account_types', fn (Blueprint $table) => $table->id());
        foreach (['customers','vendors','chart_of_accounts','bank_accounts','bank_transactions','journal_entries','journal_entry_items','customer_payments','customer_payment_allocations','credit_notes','credit_note_applications','vendor_payments','vendor_payment_allocations','debit_notes','debit_note_applications'] as $table) {
            (require glob(base_path('packages/workdo/Account/src/Database/Migrations/*create_'.$table.'_table.php'))[0])->up();
        }
        (require database_path('migrations/2026_10_14_070000_extend_mutamark_workflow_integrity.php'))->up();
        (require database_path('migrations/2026_10_14_072000_bind_document_signatures_and_audit_payloads.php'))->up();
        (require database_path('migrations/2026_10_14_075000_connect_vendor_withholding_tax.php'))->up();
        (require database_path('migrations/2026_10_14_074000_connect_distinct_purchase_orders.php'))->up();
        (require database_path('migrations/2026_10_10_232000_create_three_way_matching_tables.php'))->up();
        Schema::create('product_service_taxes',function(Blueprint $table){$table->id();$table->decimal('rate',5,2);$table->unsignedBigInteger('created_by');});
        Storage::fake('local');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function user(array $permissions = [], ?int $company = null): User
    {
        $user = User::create(['name' => 'Test User', 'email' => uniqid().'@example.com', 'type' => $company ? 'staff' : 'company', 'created_by' => $company, 'password' => 'password', 'is_enable_login' => true]);
        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'], ['add_on' => 'General', 'module' => 'srs', 'label' => $name]);
            $user->givePermissionTo($permission);
        }
        return $user;
    }

    private function document(User $owner, string $access = 'private'): Document
    {
        Storage::disk('local')->put('documents/fixture.txt', 'Original version');
        return Document::create(['document_number' => uniqid('DOC-'), 'title' => 'Contract', 'access_level' => $access, 'file_path' => 'documents/fixture.txt', 'file_name' => 'contract.txt', 'file_size' => 16, 'creator_id' => $owner->id, 'created_by' => $owner->created_by ?: $owner->id]);
    }

    public function test_document_api_requires_authentication_and_permission(): void
    {
        $this->getJson('/api/v1/documents')->assertUnauthorized();
        Sanctum::actingAs($this->user());
        $this->getJson('/api/v1/documents')->assertForbidden();
    }

    public function test_private_and_cross_company_documents_are_inaccessible(): void
    {
        $owner = $this->user();
        $document = $this->document($owner);
        Sanctum::actingAs($this->user(['manage-media', 'download-media'], $owner->id));
        $this->getJson('/api/v1/documents/'.$document->id)->assertNotFound();
        Sanctum::actingAs($this->user(['manage-media', 'download-media']));
        $this->getJson('/api/v1/documents/'.$document->id.'/download')->assertNotFound();
    }

    public function test_upload_and_replacement_preserve_downloadable_versions(): void
    {
        Sanctum::actingAs($this->user(['manage-media', 'create-media', 'download-media']));
        $response = $this->postJson('/api/v1/documents', ['title' => 'Manual', 'access_level' => 'private', 'file' => UploadedFile::fake()->createWithContent('manual.txt', 'version one')])->assertCreated();
        $id = $response->json('id');
        $oldVersion = Document::findOrFail($id)->versions()->firstOrFail();
        $this->postJson('/api/v1/documents/'.$id.'/versions', ['change_notes' => 'Updated procedure', 'file' => UploadedFile::fake()->createWithContent('manual.txt', 'version two')])->assertOk();
        $this->assertSame(2, Document::findOrFail($id)->versions()->count());
        $this->assertSame('version one', Storage::disk('local')->get($oldVersion->file_path));
        $this->getJson('/api/v1/documents/'.$id.'/download/'.$oldVersion->id)->assertOk();
    }

    public function test_shared_document_read_access_does_not_grant_replacement_rights(): void
    {
        $owner = $this->user(); $document = $this->document($owner, 'company');
        Sanctum::actingAs($this->user(['manage-media', 'create-media'], $owner->id));
        $this->getJson('/api/v1/documents/'.$document->id)->assertOk();
        $this->postJson('/api/v1/documents/'.$document->id.'/versions', ['change_notes' => 'Bad replacement', 'file' => UploadedFile::fake()->createWithContent('manual.txt', 'changed')])->assertForbidden();
    }

    public function test_executable_uploads_are_rejected(): void
    {
        Sanctum::actingAs($this->user(['create-media']));
        $this->postJson('/api/v1/documents', ['title' => 'Unsafe', 'access_level' => 'private', 'file' => UploadedFile::fake()->createWithContent('payload.php', '<?php echo 1;')])->assertUnprocessable()->assertJsonValidationErrors('file');
    }

    public function test_disabled_accounts_and_foreign_profile_edits_are_rejected(): void
    {
        $user = $this->user(); $victim = $this->user();
        Sanctum::actingAs($user);
        $this->postJson('/api/edit-profile', ['user_id' => $victim->id, 'name' => 'Changed', 'email' => 'changed@example.com'])->assertForbidden();
        $this->assertSame('Test User', $victim->fresh()->name);
        $user->update(['is_enable_login' => false]);
        Sanctum::actingAs($user->fresh());
        $this->getJson('/api/v1/documents')->assertForbidden();
    }

    public function test_proforma_totals_are_computed_and_conversion_is_idempotent(): void
    {
        $user = $this->user(['create-sales-invoices', 'edit-sales-invoices']);
        $customer = $this->user([], $user->id); $customer->update(['type' => 'client']);
        Sanctum::actingAs($user);
        $productId = DB::table('product_service_items')->insertGetId(['name' => 'Widget', 'created_by' => $user->id]);
        $warehouse = \App\Models\Warehouse::create(['name' => 'Main', 'address' => 'Test address', 'city' => 'Dar es Salaam', 'zip_code' => '00000', 'created_by' => $user->id, 'creator_id' => $user->id, 'is_active' => true]);
        $response = $this->postJson('/api/v1/proformas', ['customer_id' => $customer->id, 'issue_date' => date('Y-m-d'), 'currency' => 'TZS', 'total_amount' => 1, 'items' => [['product_id' => $productId, 'quantity' => 2, 'unit_price' => 100, 'discount_percentage' => 10, 'tax_rate' => 18]]])->assertCreated();
        $this->assertEquals(212.40, $response->json('total_amount'));
        $id = $response->json('id');
        $data = ['warehouse_id' => $warehouse->id, 'invoice_date' => date('Y-m-d'), 'due_date' => date('Y-m-d')];
        $first = $this->postJson('/api/v1/proformas/'.$id.'/convert', $data)->assertOk();
        $second = $this->postJson('/api/v1/proformas/'.$id.'/convert', $data)->assertOk();
        $this->assertSame($first->json('id'), $second->json('id'));
        $this->assertSame(1, SalesInvoice::count());
        $this->assertSame('draft', $first->json('status'));
        $this->assertEquals(212.40, $first->json('total_amount'));
        $this->assertSame('converted', ProformaInvoice::findOrFail($id)->status);
        $this->postJson('/api/v1/proformas/'.$id.'/cancel')->assertConflict();
        $this->assertSame('converted', ProformaInvoice::findOrFail($id)->status);
    }

    public function test_cancelled_proforma_cannot_be_converted_from_a_stale_instance(): void
    {
        $user = $this->user(); Sanctum::actingAs($user);
        $proforma = ProformaInvoice::create(['proforma_number' => 'PI-TEST', 'customer_name' => 'Customer', 'issue_date' => date('Y-m-d'), 'status' => 'draft', 'created_by' => $user->id, 'creator_id' => $user->id]);
        \App\Services\ProformaInvoiceService::cancel($proforma);
        try {
            \App\Services\ProformaInvoiceService::convert($proforma, []);
            $this->fail('A stale model must not bypass cancellation.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('proforma', $e->errors());
        }
        $this->assertSame('cancelled', $proforma->fresh()->status);
        $this->assertSame(0, SalesInvoice::count());
    }

    public function test_sequence_imports_existing_numbers_without_collision(): void
    {
        DB::table('documents')->insert(['document_number' => 'DOC-'.date('Y').'-00042', 'title' => 'Legacy', 'file_path' => 'old.txt', 'file_name' => 'old.txt']);
        $this->assertSame('DOC-'.date('Y').'-00043', DocumentNumberService::next('DOC'));
        $this->assertSame('DOC-'.date('Y').'-00044', DocumentNumberService::next('DOC'));
    }

    private function warehouse(User $user): \App\Models\Warehouse
    {
        return \App\Models\Warehouse::create(['name' => 'Main', 'address' => 'Test address', 'city' => 'Dar es Salaam', 'zip_code' => '00000', 'created_by' => $user->id, 'creator_id' => $user->id, 'is_active' => true]);
    }

    public function test_transfer_dispatch_and_receipt_conserve_stock_and_cannot_be_repeated(): void
    {
        $user = $this->user(); Sanctum::actingAs($user);
        $from = $this->warehouse($user); $to = $this->warehouse($user);
        $product = DB::table('product_service_items')->insertGetId(['name' => 'Widget', 'created_by' => $user->id]);
        $stock = \Workdo\ProductService\Models\WarehouseStock::create(['product_id' => $product, 'warehouse_id' => $from->id, 'quantity' => 10]);
        $transfer = \App\Models\Transfer::create(['from_warehouse' => $from->id, 'to_warehouse' => $to->id, 'product_id' => $product, 'quantity' => 5, 'date' => date('Y-m-d'), 'status' => 'draft', 'created_by' => $user->id]);
        $transfer->items()->create(['product_id' => $product, 'requested_quantity' => 5, 'dispatched_quantity' => 5]);
        $this->assertFalse($transfer->receiveOrder());
        $this->assertTrue($transfer->dispatchOrder());
        $this->assertEquals(5, $stock->fresh()->quantity);
        $this->assertFalse($transfer->dispatchOrder());
        $this->assertTrue($transfer->receiveOrder());
        $this->assertFalse($transfer->receiveOrder());
        $this->assertEquals(10, \Workdo\ProductService\Models\WarehouseStock::sum('quantity'));
    }

    public function test_short_transfer_rolls_back_all_stock_deductions(): void
    {
        $user = $this->user(); Sanctum::actingAs($user);
        $from = $this->warehouse($user); $to = $this->warehouse($user);
        $product = DB::table('product_service_items')->insertGetId(['name' => 'Widget', 'created_by' => $user->id]);
        $stock = \Workdo\ProductService\Models\WarehouseStock::create(['product_id' => $product, 'warehouse_id' => $from->id, 'quantity' => 3]);
        $transfer = \App\Models\Transfer::create(['from_warehouse' => $from->id, 'to_warehouse' => $to->id, 'product_id' => $product, 'quantity' => 5, 'date' => date('Y-m-d'), 'status' => 'draft', 'created_by' => $user->id]);
        $transfer->items()->create(['product_id' => $product, 'requested_quantity' => 5, 'dispatched_quantity' => 5]);
        try { $transfer->dispatchOrder(); $this->fail('Short stock should be rejected.'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('stock', $e->errors()); }
        $this->assertEquals(3, $stock->fresh()->quantity);
        $this->assertSame('draft', $transfer->fresh()->status);
    }

    public function test_approval_requires_assigned_actor_and_valid_state_and_honours_delegation(): void
    {
        $company = $this->user(); $requester = $this->user([], $company->id); $approver = $this->user([], $company->id); $delegate = $this->user([], $company->id);
        $proposal = \App\Models\SalesProposal::create(['proposal_number' => 'QT-1', 'proposal_date' => date('Y-m-d'), 'due_date' => date('Y-m-d'), 'customer_id' => $requester->id, 'created_by' => $company->id]);
        $approval = \App\Models\ApprovalRequest::create(['document_type' => 'sales_proposal', 'document_id' => $proposal->id, 'document_number' => 'QT-1', 'title' => 'Request', 'amount' => 100, 'requester_id' => $requester->id, 'current_approver_id' => $approver->id, 'creator_id' => $company->id, 'created_by' => $requester->id, 'status' => 'submitted']);
        Sanctum::actingAs($requester);
        $this->postJson('/api/v1/approvals/'.$approval->id.'/action', ['action' => 'approve'])->assertForbidden();
        Sanctum::actingAs($approver);
        $this->postJson('/api/v1/approvals/'.$approval->id.'/action', ['action' => 'issue'])->assertUnprocessable();
        \App\Models\ApprovalDelegation::create(['delegator_id' => $approver->id, 'delegate_id' => $delegate->id, 'start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d'), 'is_active' => true, 'created_by' => $company->id]);
        Sanctum::actingAs($delegate);
        $this->postJson('/api/v1/approvals/'.$approval->id.'/action', ['action' => 'approve'])->assertOk()->assertJsonPath('status', 'approved');
        $this->postJson('/api/v1/approvals/'.$approval->id.'/action', ['action' => 'approve'])->assertUnprocessable();
        $this->assertSame(1, $approval->actions()->count());
    }

    public function test_gateway_secrets_are_hidden_and_short_secrets_are_fully_masked(): void
    {
        $gateway = new \App\Models\PaymentGateway(['credentials' => ['api_key' => '123', 'password' => 'xy'], 'webhook_secret' => 'never-public']);
        $this->assertArrayNotHasKey('credentials', $gateway->toArray());
        $this->assertArrayNotHasKey('webhook_secret', $gateway->toArray());
        $this->assertSame('••••••••', $gateway->getMaskedCredentials()['api_key']);
    }

    public function test_approval_submission_records_actual_tier_and_prevents_duplicate_requests(): void
    {
        $user = $this->user(['create-sales-proposals']); Sanctum::actingAs($user);
        $proposal = \App\Models\SalesProposal::create(['proposal_number' => 'QT-SUBMIT', 'proposal_date' => date('Y-m-d'), 'due_date' => date('Y-m-d'), 'customer_id' => $user->id, 'total_amount' => 100, 'created_by' => $user->id]);
        \App\Models\ApprovalPolicy::create(['name' => 'Eligible tier', 'document_type' => 'sales_proposal', 'tier_level' => 3, 'tier_name' => 'Manager', 'min_amount' => 0, 'required_role' => 'manager', 'is_active' => true, 'creator_id' => $user->id, 'created_by' => $user->id]);
        $request = \Illuminate\Http\Request::create('/approvals/submit', 'POST', ['document_type' => 'sales_proposal', 'document_id' => $proposal->id]);
        $controller = app(\App\Http\Controllers\ApprovalWorkflowController::class);
        $controller->submitDocument($request);
        $controller->submitDocument($request);
        $this->assertSame(1, \App\Models\ApprovalRequest::count());
        $approval = \App\Models\ApprovalRequest::firstOrFail();
        $this->assertSame(3, $approval->current_tier);
        $this->assertEquals(3, $approval->actions()->firstOrFail()->tier_level);
        $this->assertSame(1, $approval->actions()->count());
    }

    public function test_replaying_an_already_completed_payment_is_a_no_op(): void
    {
        $user = $this->user(); Sanctum::actingAs($user);
        $payment = \App\Models\PaymentTransaction::create(['gateway_key' => 'manual', 'amount' => 100, 'status' => 'completed', 'creator_id' => $user->id, 'created_by' => $user->id]);
        $this->assertTrue($payment->markAsCompleted('retry'));
        $this->assertNull($payment->fresh()->customer_payment_id);
        $this->assertNull($payment->fresh()->gateway_transaction_id);
    }

    public function test_public_payment_claim_stays_unverified_and_cannot_overwrite_settlement(): void
    {
        // Presentation sharing queries unrelated package configuration absent from this isolated schema.
        $this->withoutMiddleware(\App\Http\Middleware\HandleInertiaRequests::class);
        $user = $this->user();
        \App\Models\PaymentGateway::create(['gateway_key' => 'bank_transfer', 'name' => 'Bank', 'is_active' => true, 'creator_id' => $user->id, 'created_by' => $user->id]);
        $payment = \App\Models\PaymentTransaction::create(['gateway_key' => 'bank_transfer', 'amount' => 100, 'status' => 'pending', 'creator_id' => $user->id, 'created_by' => $user->id]);
        $data = ['gateway_key' => 'bank_transfer', 'payment_channel' => 'bank', 'reference_number' => 'CUSTOMER-CLAIM'];
        $this->post('/pay/checkout/'.$payment->checkout_token, $data)->assertRedirect();
        $this->assertSame('pending_verification', $payment->fresh()->status);
        $this->assertNull($payment->fresh()->customer_payment_id);
        $this->get('/pay/checkout/'.$payment->checkout_token.'/success')->assertConflict();
        $payment->update(['status' => 'completed', 'gateway_transaction_id' => 'PROVIDER-VERIFIED']);
        $this->post('/pay/checkout/'.$payment->checkout_token, $data)->assertConflict();
        $this->assertSame('completed', $payment->fresh()->status);
        $this->assertSame('PROVIDER-VERIFIED', $payment->fresh()->gateway_transaction_id);
    }

    public function test_webhook_rejects_unsigned_and_mismatched_amount_callbacks(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\HandleInertiaRequests::class);
        $user = $this->user();
        \App\Models\PaymentGateway::create(['gateway_key' => 'test_gateway', 'name' => 'Test', 'is_active' => true, 'webhook_secret' => 'test-signature-secret', 'creator_id' => $user->id, 'created_by' => $user->id]);
        $payment = \App\Models\PaymentTransaction::create(['gateway_key' => 'test_gateway', 'amount' => 100, 'currency' => 'TZS', 'status' => 'pending', 'creator_id' => $user->id, 'created_by' => $user->id]);
        $data = ['transaction_reference' => $payment->transaction_reference, 'status' => 'completed', 'amount' => 99, 'currency' => 'TZS', 'gateway_transaction_id' => 'PROVIDER-1'];
        $this->postJson('/api/v1/payments/webhook/test_gateway', $data)->assertUnauthorized();
        $body = json_encode($data);
        $this->call('POST', '/api/v1/payments/webhook/test_gateway', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json', 'HTTP_X_PAYMENT_SIGNATURE' => hash_hmac('sha256', $body, 'test-signature-secret')], $body)->assertUnprocessable();
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertNull($payment->fresh()->customer_payment_id);
    }

    public function test_encrypted_backup_verification_detects_tampering_without_executing_sql(): void
    {
        $service = app(\App\Services\DatabaseBackupService::class);
        $path = Storage::disk('local')->path('test.sql.enc');
        $sql = str_repeat('sensitive test SQL ', 5000);
        $service->encrypt($path, [$sql]);
        $this->assertSame(strlen($sql), $service->verify($path)['plaintext_bytes']);
        $ciphertext = file_get_contents($path);
        $this->assertStringNotContainsString('sensitive test SQL', $ciphertext);
        $ciphertext[100] = chr(ord($ciphertext[100]) ^ 1);
        file_put_contents($path, $ciphertext);
        $this->expectException(\RuntimeException::class);
        $service->verify($path);
    }

    public function test_authenticator_codes_match_standard_vector_and_cannot_replay(): void
    {
        $this->assertSame('287082', User::totp('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', 1));
        $user = $this->user();
        $secret = User::newTwoFactorSecret();
        $user->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => now(), 'two_factor_recovery_codes' => [\Illuminate\Support\Facades\Hash::make('recovery-test-code')]])->save();
        $code = User::totp($secret, intdiv(time(), 30));
        $this->assertTrue($user->verifySecondFactor($code));
        $this->assertFalse($user->verifySecondFactor($code));
        $this->assertTrue($user->verifySecondFactor('recovery-test-code'));
        $this->assertFalse($user->verifySecondFactor('recovery-test-code'));
        $this->assertStringNotContainsString($secret, DB::table('users')->where('id',$user->id)->value('two_factor_secret'));
    }

    public function test_signed_document_content_changes_invalidate_verification(): void
    {
        $user = $this->user(); Sanctum::actingAs($user);
        $proposal = \App\Models\SalesProposal::create(['proposal_number' => 'QT-SIGNED', 'proposal_date' => date('Y-m-d'), 'due_date' => date('Y-m-d'), 'customer_id' => $user->id, 'notes' => 'Original agreed terms', 'created_by' => $user->id]);
        $record = \App\Models\DocumentVerification::create(['document_type' => 'quotation', 'document_id' => $proposal->id, 'document_number' => 'QT-SIGNED', 'verification_hash' => str_repeat('a',64), 'created_by' => $user->id]);
        $signed = \App\Services\DocumentVerificationService::bindSignature($record,$proposal,$user->id);
        $this->assertNotNull(\App\Services\DocumentVerificationService::findByHash($signed->verification_hash));
        $this->assertArrayNotHasKey('signed_payload',$signed->toArray());
        $proposal->update(['notes' => 'Changed terms']);
        $this->assertNull(\App\Services\DocumentVerificationService::findByHash($signed->verification_hash));
    }

    public function test_audit_payload_tampering_is_detected_and_model_changes_are_forbidden(): void
    {
        Sanctum::actingAs($this->user());
        $log = \App\Models\FinancialAuditLog::record(['event_type'=>'SETTINGS_CHANGED','module'=>'Administration','reference_number'=>'SETTINGS','action'=>'Changed setting','old_values'=>['value'=>1],'new_values'=>['value'=>2]]);
        $this->assertTrue(\App\Services\FinancialControlService::verifyAuditChainIntegrity()['is_valid']);
        DB::table('financial_audit_logs')->where('id',$log->id)->update(['new_values'=>json_encode(['value'=>999])]);
        $this->assertFalse(\App\Services\FinancialControlService::verifyAuditChainIntegrity()['is_valid']);
        $this->expectException(\LogicException::class);
        $log->delete();
    }

    public function test_delivery_shortage_rolls_back_and_repeat_dispatch_does_not_deduct_twice(): void
    {
        $user=$this->user(); Sanctum::actingAs($user); $warehouse=$this->warehouse($user);
        $product=DB::table('product_service_items')->insertGetId(['name'=>'Part','created_by'=>$user->id]);
        $stock=\Workdo\ProductService\Models\WarehouseStock::create(['product_id'=>$product,'warehouse_id'=>$warehouse->id,'quantity'=>3]);
        $delivery=\App\Models\DeliveryNote::create(['delivery_number'=>'DN-TEST','delivery_date'=>date('Y-m-d'),'customer_id'=>$user->id,'warehouse_id'=>$warehouse->id,'status'=>'draft','creator_id'=>$user->id,'created_by'=>$user->id]);
        $item=$delivery->items()->create(['product_id'=>$product,'product_name'=>'Part','quantity_ordered'=>5,'quantity_shipped'=>5]);
        try { \App\Services\DeliveryNoteService::dispatchDeliveryNote($delivery,$user->id); $this->fail('Short stock must fail.'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('stock',$e->errors()); }
        $this->assertEquals(3,$stock->fresh()->quantity);
        $item->update(['quantity_shipped'=>2]);
        \App\Services\DeliveryNoteService::dispatchDeliveryNote($delivery,$user->id);
        \App\Services\DeliveryNoteService::dispatchDeliveryNote($delivery,$user->id);
        $this->assertEquals(1,$stock->fresh()->quantity);
    }

    public function test_paid_invoices_cannot_be_deleted_and_subscription_routes_are_absent(): void
    {
        $user=$this->user(['delete-sales-invoices']); Sanctum::actingAs($user);
        $invoice=SalesInvoice::create(['invoice_number'=>'INV-PAID','invoice_date'=>date('Y-m-d'),'due_date'=>date('Y-m-d'),'customer_id'=>$user->id,'status'=>'paid','created_by'=>$user->id]);
        $this->withoutMiddleware([\App\Http\Middleware\HandleInertiaRequests::class, \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class]);
        $this->delete('/sales-invoices/'.$invoice->id)->assertRedirect();
        $this->assertNotNull($invoice->fresh());
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('plans.index'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('subscriptions.store'));
    }

    private function account(User $user,string $code,string $normal='debit'): \Workdo\Account\Models\ChartOfAccount
    {
        $type=DB::table('account_types')->insertGetId([]);
        return \Workdo\Account\Models\ChartOfAccount::create(['account_code'=>$code,'account_name'=>$code,'account_type_id'=>$type,'normal_balance'=>$normal,'current_balance'=>0,'created_by'=>$user->id,'creator_id'=>$user->id]);
    }

    public function test_cancelled_delivery_restores_order_fulfillment_once(): void
    {
        $user=$this->user(); Sanctum::actingAs($user); $warehouse=$this->warehouse($user);
        $product=DB::table('product_service_items')->insertGetId(['name'=>'Part','created_by'=>$user->id]);
        $stock=\Workdo\ProductService\Models\WarehouseStock::create(['product_id'=>$product,'warehouse_id'=>$warehouse->id,'quantity'=>5]);
        $order=\App\Models\SalesOrder::create(['order_number'=>'SO-CANCEL','order_date'=>date('Y-m-d'),'customer_id'=>$user->id,'warehouse_id'=>$warehouse->id,'status'=>'confirmed','created_by'=>$user->id]);
        $line=$order->items()->create(['product_id'=>$product,'product_name'=>'Part','quantity'=>2,'unit_price'=>10,'total_amount'=>20]);
        $delivery=\App\Models\DeliveryNote::create(['delivery_number'=>'DN-CANCEL','sales_order_id'=>$order->id,'delivery_date'=>date('Y-m-d'),'customer_id'=>$user->id,'warehouse_id'=>$warehouse->id,'status'=>'draft','created_by'=>$user->id]);
        $delivery->items()->create(['product_id'=>$product,'product_name'=>'Part','quantity_ordered'=>2,'quantity_shipped'=>2]);
        \App\Services\DeliveryNoteService::dispatchDeliveryNote($delivery,$user->id);
        $this->assertEquals(2,$line->fresh()->fulfilled_quantity);
        \App\Services\DeliveryNoteService::cancelDeliveryNote($delivery,$user->id);
        \App\Services\DeliveryNoteService::cancelDeliveryNote($delivery,$user->id);
        $this->assertEquals(0,$line->fresh()->fulfilled_quantity);
        $this->assertEquals(5,$stock->fresh()->quantity);
        $this->assertSame('confirmed',$order->fresh()->status);
    }

    public function test_credit_limit_blocks_posting_and_closed_period_blocks_journals(): void
    {
        $user=$this->user(['post-sales-invoices']); Sanctum::actingAs($user);
        $customer=\Workdo\Account\Models\Customer::create(['customer_code'=>'CREDIT','user_id'=>$user->id,'company_name'=>'Customer','contact_person_name'=>'Contact','contact_person_email'=>'credit@example.test','credit_limit'=>50,'created_by'=>$user->id]);
        $invoice=SalesInvoice::create(['invoice_number'=>'INV-CREDIT','invoice_date'=>date('Y-m-d'),'due_date'=>date('Y-m-d'),'customer_id'=>$user->id,'status'=>'draft','total_amount'=>100,'balance_amount'=>100,'created_by'=>$user->id]);
        $product=DB::table('product_service_items')->insertGetId(['name'=>'Credit product','created_by'=>$user->id]);
        $invoice->items()->create(['product_id'=>$product,'quantity'=>1,'unit_price'=>100]);
        try { \App\Services\FinancialControlService::postInvoice($invoice); $this->fail('Credit limit must block posting.'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('credit_limit',$e->errors()); }
        $this->assertSame('draft',$invoice->fresh()->status);
        \App\Models\Setting::create(['key'=>'accounting_locked_until','value'=>date('Y-m-d'),'created_by'=>$user->id]);
        try { \Workdo\Account\Models\JournalEntry::create(['journal_number'=>'JV-LOCKED','journal_date'=>date('Y-m-d'),'status'=>'posted','created_by'=>$user->id]); $this->fail('Closed period must block journals.'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('journal_date',$e->errors()); }
        $this->assertSame(0,DB::table('journal_entries')->count());
    }

    public function test_customer_revision_reopens_existing_quotation_for_sales_editing(): void
    {
        $company=$this->user(); $customer=$this->user([],$company->id); $customer->update(['type'=>'client']); Sanctum::actingAs($customer);
        $proposal=\App\Models\SalesProposal::create(['proposal_number'=>'QT-REVISION','proposal_date'=>date('Y-m-d'),'due_date'=>date('Y-m-d'),'customer_id'=>$customer->id,'status'=>'sent','total_amount'=>100,'created_by'=>$company->id]);
        $request=\Illuminate\Http\Request::create('/portal/customer/proposals/'.$proposal->id.'/reject','POST',['reason'=>'Change quantity to two.']);
        app(\App\Http\Controllers\PortalController::class)->customerRejectProposal($request,$proposal->id);
        $this->assertSame('draft',$proposal->fresh()->status);
        $this->assertStringContainsString('Change quantity to two.',$proposal->fresh()->notes);
        $this->assertEquals(100,$proposal->fresh()->total_amount);
    }

    public function test_manual_customer_clearance_posts_once_and_rejects_overpayment(): void
    {
        $user=$this->user(['cleared-customer-payments']); Sanctum::actingAs($user);
        \Illuminate\Support\Facades\Event::fake([\Workdo\Account\Events\UpdateBudgetSpending::class]);
        $bankAccount=$this->account($user,'1110'); $this->account($user,'1100');
        $bank=\Workdo\Account\Models\BankAccount::create(['account_name'=>'Cash','bank_name'=>'Bank','account_number'=>'TEST','gl_account_id'=>$bankAccount->id,'current_balance'=>100,'opening_balance'=>100,'is_active'=>true,'created_by'=>$user->id]);
        $invoice=SalesInvoice::create(['invoice_number'=>'INV-CLEAR','invoice_date'=>date('Y-m-d'),'due_date'=>date('Y-m-d'),'customer_id'=>$user->id,'status'=>'posted','total_amount'=>100,'balance_amount'=>100,'created_by'=>$user->id]);
        $payment=\Workdo\Account\Models\CustomerPayment::create(['payment_date'=>date('Y-m-d'),'customer_id'=>$user->id,'bank_account_id'=>$bank->id,'payment_amount'=>100,'status'=>'pending','created_by'=>$user->id,'creator_id'=>$user->id]);
        $payment->allocations()->create(['invoice_id'=>$invoice->id,'allocated_amount'=>100]);
        \App\Services\FinancialControlService::clearAccountPayment($payment,'cleared');
        \App\Services\FinancialControlService::clearAccountPayment($payment,'cleared');
        $this->assertEquals(100,$invoice->fresh()->paid_amount);
        $this->assertEquals(200,$bank->fresh()->current_balance);
        $this->assertSame(1,DB::table('journal_entries')->where('reference_type','customer_payment')->count());
        $this->assertSame(1,DB::table('bank_transactions')->count());
        $other=\Workdo\Account\Models\CustomerPayment::create(['payment_date'=>date('Y-m-d'),'customer_id'=>$user->id,'bank_account_id'=>$bank->id,'payment_amount'=>1,'status'=>'pending','created_by'=>$user->id]);
        $other->allocations()->create(['invoice_id'=>$invoice->id,'allocated_amount'=>1]);
        try { \App\Services\FinancialControlService::clearAccountPayment($other,'cleared'); $this->fail('Paid invoice cannot receive a further allocation.'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('allocations',$e->errors()); }
        $this->assertSame('pending',$other->fresh()->status);
        $this->assertEquals(200,$bank->fresh()->current_balance);
    }

    public function test_payment_api_uses_company_and_actor_columns_correctly(): void
    {
        $company=$this->user(['manage-customer-payments','manage-any-customer-payments']); $staff=$this->user([], $company->id); $other=$this->user();
        $owned=\App\Models\PaymentTransaction::create(['gateway_key'=>'manual','amount'=>50,'status'=>'pending','creator_id'=>$company->id,'created_by'=>$staff->id]);
        \App\Models\PaymentTransaction::create(['gateway_key'=>'manual','amount'=>90,'status'=>'pending','creator_id'=>$other->id,'created_by'=>$other->id]);
        Sanctum::actingAs($company);
        $response=$this->getJson('/api/v1/payments')->assertOk();
        $this->assertSame([$owned->id],array_column($response->json('data.items'),'id'));
    }

    public function test_gateway_settlement_posts_actual_receipt_journal_and_bank_only_once(): void
    {
        $company=$this->user(); $actor=$this->user([],$company->id); Sanctum::actingAs($company);
        \Illuminate\Support\Facades\Event::fake([\Workdo\Account\Events\UpdateBudgetSpending::class]);
        $bankGl=$this->account($company,'1110'); $this->account($company,'1100');
        $bank=\Workdo\Account\Models\BankAccount::create(['account_name'=>'Gateway','bank_name'=>'Bank','account_number'=>'ONLINE','gl_account_id'=>$bankGl->id,'current_balance'=>0,'is_active'=>true,'created_by'=>$company->id]);
        \App\Models\PaymentGateway::create(['gateway_key'=>'verified-test','name'=>'Verified test','is_active'=>true,'credentials'=>['bank_account_id'=>$bank->id],'created_by'=>$company->id]);
        $invoice=SalesInvoice::create(['invoice_number'=>'INV-GATEWAY','invoice_date'=>date('Y-m-d'),'due_date'=>date('Y-m-d'),'customer_id'=>$actor->id,'status'=>'posted','total_amount'=>100,'balance_amount'=>100,'created_by'=>$company->id]);
        $payment=\App\Models\PaymentTransaction::create(['gateway_key'=>'verified-test','invoice_type'=>'sales_invoice','invoice_id'=>$invoice->id,'amount'=>100,'currency'=>'TZS','status'=>'pending','creator_id'=>$company->id,'created_by'=>$actor->id]);
        $this->assertTrue($payment->markAsCompleted('PROVIDER-TEST-REF'));
        $this->assertTrue($payment->markAsCompleted('PROVIDER-TEST-REF'));
        $this->assertEquals(100,$invoice->fresh()->paid_amount); $this->assertEquals(100,$bank->fresh()->current_balance);
        $this->assertSame(1,DB::table('customer_payments')->count()); $this->assertSame(1,DB::table('customer_payment_allocations')->count());
        $this->assertSame(1,DB::table('journal_entries')->count()); $this->assertSame(1,DB::table('bank_transactions')->count());
        $this->assertEquals($company->id,\App\Models\DocumentVerification::where('document_type','receipt_voucher')->value('created_by'));
    }

    public function test_customer_write_api_preserves_credit_limit_and_rejects_other_company_users(): void
    {
        $company=$this->user(['create-customers','edit-customers','manage-account']); Sanctum::actingAs($company);
        \Illuminate\Support\Facades\Event::fake([\Workdo\Account\Events\CreateCustomer::class,\Workdo\Account\Events\UpdateCustomer::class]);
        $client=$this->user([],$company->id); $client->update(['type'=>'client']);
        $address=['name'=>'Customer','address_line_1'=>'Main street','city'=>'Dar es Salaam','state'=>'Dar','country'=>'Tanzania','zip_code'=>'11101'];
        $data=['user_id'=>$client->id,'company_name'=>'API customer','contact_person_name'=>'Contact','contact_person_email'=>'api@example.test','credit_limit'=>500,'billing_address'=>$address,'same_as_billing'=>true];
        $response=$this->postJson('/api/v1/customers',$data)->assertCreated(); $id=$response->json('id');
        unset($data['credit_limit']); $data['company_name']='Updated API customer';
        $this->putJson('/api/v1/customers/'.$id,$data)->assertOk();
        $this->assertEquals(500,\Workdo\Account\Models\Customer::findOrFail($id)->credit_limit);
        $foreign=$this->user(); $foreign->update(['type'=>'client']); $data['user_id']=$foreign->id;
        $this->postJson('/api/v1/customers',$data)->assertUnprocessable()->assertJsonValidationErrors('user_id');
        $this->assertSame(1,DB::table('customers')->count());
    }

    public function test_partial_purchase_receipts_match_before_payment_and_withholding_posts_once(): void
    {
        $user=$this->user(['post-grn','create-purchase-invoices','cleared-vendor-payments','manage-account']); Sanctum::actingAs($user);
        \Illuminate\Support\Facades\Event::fake([\Workdo\Account\Events\UpdateBudgetSpending::class]);
        $vendorUser=$this->user([],$user->id); $vendorUser->update(['type'=>'vendor']);
        $vendor=\Workdo\Account\Models\Vendor::create(['user_id'=>$vendorUser->id,'vendor_code'=>'V-TEST','company_name'=>'Supplier','contact_person_name'=>'Contact','created_by'=>$user->id]);
        $warehouse=$this->warehouse($user); $product=DB::table('product_service_items')->insertGetId(['name'=>'Material','created_by'=>$user->id]);
        $order=\App\Services\ThreeWayMatchService::createPurchaseOrder(['vendor_id'=>$vendor->id,'warehouse_id'=>$warehouse->id,'items'=>[['product_id'=>$product,'quantity'=>10,'unit_price'=>10]]]);
        $order->update(['status'=>'issued']);
        $invoice=\App\Services\ThreeWayMatchService::invoicePurchaseOrder($order);
        $this->assertSame($invoice->id,\App\Services\ThreeWayMatchService::invoicePurchaseOrder($order)->id);
        foreach ([4,6] as $index=>$quantity) {
            $grn=\App\Models\GoodsReceivedNote::create(['grn_number'=>'GRN-PART-'.$index,'purchase_order_id'=>$order->id,'purchase_invoice_id'=>$invoice->id,'vendor_id'=>$vendor->id,'warehouse_id'=>$warehouse->id,'grn_date'=>date('Y-m-d'),'status'=>'received','inspection_status'=>'inspected_passed','created_by'=>$user->id]);
            $grn->items()->create(['product_id'=>$product,'ordered_quantity'=>10,'received_quantity'=>$quantity,'accepted_quantity'=>$quantity,'unit_price'=>10,'total_amount'=>$quantity*10]);
            $this->postJson('/api/v1/goods-received-notes/'.$grn->id.'/post')->assertOk();
            $this->postJson('/api/v1/goods-received-notes/'.$grn->id.'/post')->assertOk();
            if ($index===0) {
                try { \App\Services\ThreeWayMatchService::assertInvoicePayable($invoice); $this->fail('Incomplete receipt must hold payment.'); }
                catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('invoice',$e->errors()); }
            }
        }
        $this->assertEquals(10,\Workdo\ProductService\Models\WarehouseStock::sum('quantity'));
        (new \Workdo\ProductService\Listeners\PostPurchaseInvoiceListener())->handle(new \App\Events\PostPurchaseInvoice($invoice));
        $this->assertEquals(10,\Workdo\ProductService\Models\WarehouseStock::sum('quantity'));
        \App\Services\ThreeWayMatchService::assertInvoicePayable($invoice);
        $invoice->update(['status'=>'posted']);
        $bankGl=$this->account($user,'1110'); $this->account($user,'2000','credit'); $wht=$this->account($user,'2200','credit');
        \App\Models\Setting::create(['key'=>'withholding_payable_account_id','value'=>(string)$wht->id,'created_by'=>$user->id]);
        $bank=\Workdo\Account\Models\BankAccount::create(['account_name'=>'Cash','bank_name'=>'Bank','account_number'=>'TEST','gl_account_id'=>$bankGl->id,'current_balance'=>1000,'is_active'=>true,'created_by'=>$user->id]);
        $payment=\Workdo\Account\Models\VendorPayment::create(['payment_date'=>date('Y-m-d'),'vendor_id'=>$vendorUser->id,'bank_account_id'=>$bank->id,'payment_amount'=>90,'wht_base_amount'=>100,'wht_rate'=>10,'wht_amount'=>10,'status'=>'pending','created_by'=>$user->id,'creator_id'=>$user->id]);
        $payment->allocations()->create(['invoice_id'=>$invoice->id,'allocated_amount'=>100]);
        \App\Services\FinancialControlService::clearAccountPayment($payment,'cleared');
        \App\Services\FinancialControlService::clearAccountPayment($payment,'cleared');
        $this->assertEquals(100,$invoice->fresh()->paid_amount); $this->assertEquals(910,$bank->fresh()->current_balance);
        $tax=DB::table('withholding_taxes')->first(); $this->assertEquals(10,$tax->wht_amount);
        $this->postJson('/api/v1/withholding/'.$tax->id.'/remit',['bank_account_id'=>$bank->id,'remittance_date'=>date('Y-m-d')])->assertOk();
        $this->postJson('/api/v1/withholding/'.$tax->id.'/remit',['bank_account_id'=>$bank->id,'remittance_date'=>date('Y-m-d')])->assertOk();
        $this->assertEquals(900,$bank->fresh()->current_balance); $this->assertSame('remitted',DB::table('withholding_taxes')->value('status'));
    }
}
