<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funnels', function (Blueprint $table) {
            $table->json('default_tags')->nullable()->after('description');
        });

        Schema::table('funnel_steps', function (Blueprint $table) {
            $table->json('step_tags')->nullable()->after('template_data');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('source_campaign');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('tags');
        });

        Schema::table('funnel_steps', function (Blueprint $table) {
            $table->dropColumn('step_tags');
        });

        Schema::table('funnels', function (Blueprint $table) {
            $table->dropColumn('default_tags');
        });
    }
};
