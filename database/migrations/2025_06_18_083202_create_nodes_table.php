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
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topology_id')->constrained(); // Laravel otomatis ke table 'topologies'
            $table->string('name')->nullable();
            $table->enum('type', ['OLT', 'Splitter', 'ONT']);
            $table->integer('x');
            $table->integer('y');
            $table->double('initial_power')->nullable();
            $table->double('final_power')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
