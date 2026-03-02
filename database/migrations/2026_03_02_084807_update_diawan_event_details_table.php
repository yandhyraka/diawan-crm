<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('diawan_event_details', function (Blueprint $table) {
            $table->string('event_detail_data')->after('event_detail_human_uuid');

            $table->dropColumn('event_detail_item');
            $table->dropColumn('event_detail_amount');
            $table->dropColumn('event_detail_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('diawan_event_details', function (Blueprint $table) {
            $table->dropColumn('event_detail_data');

            $table->string('event_detail_item')->after('event_detail_human_uuid');
            $table->double('event_detail_amount')->nullable(true)->after('event_detail_item');
            $table->double('event_detail_price')->nullable(true)->after('event_detail_amount');
        });
    }
};
