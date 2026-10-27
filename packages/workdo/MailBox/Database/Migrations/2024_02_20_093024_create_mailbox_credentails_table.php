<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('mailbox_credentails')) {
            Schema::create('mailbox_credentails', function (Blueprint $table) {
                $table->id();
                $table->string('emailbox_mail_driver')->unique();
                $table->string('emailbox_mail_host')->unique();
                $table->string('emailbox_outgoing_port')->unique();
                $table->string('emailbox_incoming_port')->unique();
                $table->string('emailbox_mail_username')->unique();
                $table->string('emailbox_mail_from_address')->unique();
                $table->string('emailbox_mail_password')->nullable();
                $table->string('emailbox_mail_encryption')->nullable();  
                $table->string('emailbox_mail_from_name')->nullable();  
                $table->integer('workspace_id')->default(0);
                $table->integer('created_by')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mailbox_credentails');
    }
};
