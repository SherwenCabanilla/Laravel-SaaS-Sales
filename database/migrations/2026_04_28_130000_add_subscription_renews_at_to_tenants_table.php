<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->timestamp('subscription_renews_at')->nullable()->after('subscription_activated_at');
        });

        DB::table('tenants')
            ->where('status', 'active')
            ->whereNull('subscription_renews_at')
            ->whereNotNull('subscription_activated_at')
            ->update([
                'subscription_renews_at' => DB::raw('DATE_ADD(subscription_activated_at, INTERVAL 1 MONTH)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('subscription_renews_at');
        });
    }
};
