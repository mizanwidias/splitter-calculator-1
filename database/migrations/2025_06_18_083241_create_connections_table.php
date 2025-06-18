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
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topology_id')->constrained('topologies')->onDelete('cascade');
            $table->foreignId('node_from_id')->constrained('nodes')->onDelete('cascade');
            $table->foreignId('node_to_id')->constrained('nodes')->onDelete('cascade');
            $table->string('cable_type')->nullable();
            $table->string('cable_color')->default('black');
            $table->double('loss_value')->default(0);
            $table->double('length')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connections');
    }
};
