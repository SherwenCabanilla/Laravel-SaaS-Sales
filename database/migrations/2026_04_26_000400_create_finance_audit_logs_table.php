<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_receipt_id')->nullable()->constrained('payment_receipts')->nullOnDelete();
            $table->string('event_type', 100);
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'event_type']);
            $table->index(['actor_user_id', 'event_type']);
            $table->index(['payment_id', 'event_type']);
            $table->index(['payment_receipt_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_audit_logs');
    }
};
