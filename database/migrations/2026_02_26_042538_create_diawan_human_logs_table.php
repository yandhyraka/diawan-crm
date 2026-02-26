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
        Schema::create('diawan_human_logs', function (Blueprint $table) {
            $table->id('human_log_id');
            $table->uuid('human_log_human_uuid');
            $table->string('human_log_log_type');
            $table->text('human_log_before')->nullable(true);
            $table->text('human_log_after');
            $table->string('human_log_input_source');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diawan_human_logs');
    }
};
