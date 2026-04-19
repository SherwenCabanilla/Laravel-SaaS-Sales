<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnel_builder_telemetry_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 32);
            $table->string('event', 120);
            $table->unsignedInteger('latency_ms')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['source', 'event', 'created_at'], 'fbte_source_event_created_idx');
            $table->index(['tenant_id', 'created_at'], 'fbte_tenant_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_builder_telemetry_events');
    }
};

