<?php
// database/migrations/xxxx_xx_xx_create_topologis_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topologies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('labs')->onDelete('cascade'); // FK
            $table->string('name')->default('Topologi Default');
            $table->boolean('is_autosaved')->default(false);
            $table->json('nodes')->nullable();
            $table->json('connections')->nullable();
            $table->float('power')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topologies');
    }
};
