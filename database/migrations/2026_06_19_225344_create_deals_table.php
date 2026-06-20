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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string("name")->nullable(false);
            $table->integer("amount")->nullable(false);
            $table->string("pipeline")->nullable(false);
            $table->string("stage")->nullable(false);
            $table->string("client_id")->nullable(false);
            $table->string("client_provider")->nullable(false);
            $table->timestamp("last_client_updated")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
