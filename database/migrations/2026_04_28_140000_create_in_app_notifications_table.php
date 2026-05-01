<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('in_app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 40)->default('n8n');
            $table->string('event_name', 160);
            $table->string('level', 20)->default('info');
            $table->string('idempotency_key', 191);
            $table->string('title', 255);
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'idempotency_key'], 'in_app_notifications_user_idempotency_unique');
            $table->index(['user_id', 'read_at']);
            $table->index(['tenant_id', 'event_name']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('in_app_notifications');
    }
};
