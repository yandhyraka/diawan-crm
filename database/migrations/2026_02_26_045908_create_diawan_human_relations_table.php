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
        Schema::create('diawan_human_relations', function (Blueprint $table) {
            $table->id('human_relation_id');
            $table->uuid('human_relation_human_uuid1');
            $table->uuid('human_relation_human_uuid2');
            $table->string('human_relation_relation_type');
            $table->text('human_relation_data')->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diawan_human_relations');
    }
};
