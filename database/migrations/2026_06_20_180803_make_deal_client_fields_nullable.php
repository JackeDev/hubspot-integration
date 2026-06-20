<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->string('client_id')->nullable()->change();
            $table->string('client_provider')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('deals')
            ->whereNull('client_id')
            ->orWhereNull('client_provider')
            ->delete();

        Schema::table('deals', function (Blueprint $table) {
            $table->string('client_id')->nullable(false)->change();
            $table->string('client_provider')->nullable(false)->change();
        });
    }
};
