<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_payout_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('destination_type', 40)->default('gcash');
            $table->string('account_name', 160);
            $table->text('destination_value')->nullable();
            $table->string('masked_destination', 80)->nullable();
            $table->string('provider_destination_reference', 160)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_default')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'destination_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_payout_accounts');
    }
};
