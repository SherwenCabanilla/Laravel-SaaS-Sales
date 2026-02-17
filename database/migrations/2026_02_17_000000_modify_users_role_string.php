<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Change enum to string to support hyphenated slugs like 'account-owner'
            $table->string('role')->default('customer')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert back to enum if needed (warning: data loss if values don't match)
            $table->enum('role', ['super_admin','account_owner','marketing_manager','sales_agent','finance','customer'])->default('customer')->change();
        });
    }
};
