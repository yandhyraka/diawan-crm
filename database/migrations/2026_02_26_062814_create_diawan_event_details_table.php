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
        Schema::create('diawan_event_details', function (Blueprint $table) {
            $table->id('event_detail_id');
            $table->integer('event_detail_event_id');
            $table->string('event_detail_item');
            $table->double('event_detail_amount')->nullable(true);
            $table->double('event_detail_price')->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diawan_event_details');
    }
};
