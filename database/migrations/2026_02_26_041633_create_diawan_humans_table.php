<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('diawan_humans', function (Blueprint $table) {
            $table->uuid('human_uuid')->primary();
            $table->string('human_first_name');
            $table->string('human_last_name')->nullable(true);
            $table->string('human_ktp')->nullable(true);
            $table->date('human_birth_date')->nullable(true);
            $table->string('human_phone_number')->nullable(true);
            $table->string('human_email')->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('diawan_humans');
    }
};
