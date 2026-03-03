<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("
                ALTER TABLE funnel_steps
                MODIFY COLUMN type ENUM('landing','opt_in','sales','checkout','upsell','downsell','thank_you','custom')
                NOT NULL
            ");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("
                ALTER TABLE funnel_steps
                MODIFY COLUMN type ENUM('landing','opt_in','sales','checkout','upsell','downsell','thank_you')
                NOT NULL
            ");
        }
    }
};

