<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_payout_accounts', function (Blueprint $table) {
            $table->string('verification_status', 40)
                ->default('pending_platform_review')
                ->after('verified_by');
            $table->timestamp('reviewed_at')->nullable()->after('verification_status');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable()->after('reviewed_by');
            $table->index('verification_status');
        });

        DB::table('tenant_payout_accounts')
            ->where('is_verified', true)
            ->update([
                'verification_status' => 'approved',
                'reviewed_at' => DB::raw('COALESCE(verified_at, updated_at, created_at)'),
                'reviewed_by' => DB::raw('verified_by'),
            ]);

        DB::table('tenant_payout_accounts')
            ->where('is_verified', false)
            ->update([
                'verification_status' => 'pending_platform_review',
            ]);
    }

    public function down(): void
    {
        Schema::table('tenant_payout_accounts', function (Blueprint $table) {
            $table->dropIndex(['verification_status']);
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['verification_status', 'reviewed_at', 'review_notes']);
        });
    }
};
