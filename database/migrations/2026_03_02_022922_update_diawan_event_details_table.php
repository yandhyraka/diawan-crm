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
            $table->uuid('event_detail_human_uuid')->after('event_detail_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('diawan_event_details', function (Blueprint $table) {
            $table->dropColumn('event_detail_human_uuid');
        });
    }
};
