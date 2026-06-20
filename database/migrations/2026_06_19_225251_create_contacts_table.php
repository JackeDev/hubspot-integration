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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string("first_name")->nullable(false);
            $table->string("last_name")->nullable(false);
            $table->string("email")->nullable(false)->unique();
            $table->string("phone")->nullable();
            $table->string("client_id")->nullable(false);
            $table->string("client_provider")->nullable(false);
            $table->timestamp("last_client_updated")->nullable();
            $table->timestamps();

            $table->unique(["client_id", "client_provider"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
