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
        Schema::create('webhook_contact_events', function (Blueprint $table) {
            $table->id();
            $table->string("event_id")->nullable(false)->unique();
            $table->string("contact_id")->nullable(false);
            $table->json('payload')->nullable(false);
            $table->timestamp('processed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_contact_events');
    }
};
