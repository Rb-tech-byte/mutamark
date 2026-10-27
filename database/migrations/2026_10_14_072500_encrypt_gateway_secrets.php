<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration {
    public function up(): void {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->longText('credentials')->nullable()->change();
            $table->text('webhook_secret')->nullable()->change();
        });
        foreach (DB::table('payment_gateways')->orderBy('id')->get() as $gateway) {
            $updates = [];
            foreach (['credentials','webhook_secret'] as $field) {
                if ($gateway->$field === null || $gateway->$field === '') continue;
                try { Crypt::decryptString($gateway->$field); }
                catch (\Illuminate\Contracts\Encryption\DecryptException $e) { $updates[$field] = Crypt::encryptString($gateway->$field); }
            }
            if ($updates) DB::table('payment_gateways')->where('id', $gateway->id)->update($updates);
        }
    }
    public function down(): void { /* Never automatically downgrade stored secrets to plaintext. */ }
};
