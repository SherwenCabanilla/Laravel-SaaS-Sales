<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120)->default('Default Commission Plan');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(true);
            $table->decimal('gateway_fee_rate', 5, 2)->default(3.00);
            $table->decimal('platform_fee_rate', 5, 2)->default(2.00);
            $table->decimal('sales_agent_rate', 5, 2)->default(7.00);
            $table->decimal('marketing_manager_rate', 5, 2)->default(3.00);
            $table->unsignedSmallInteger('hold_days')->default(7);
            $table->string('sales_attribution_model', 60)->default('assigned_lead');
            $table->string('marketing_attribution_model', 60)->default('last_touch_campaign');
            $table->foreignId('default_marketing_manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_plans');
    }
};
