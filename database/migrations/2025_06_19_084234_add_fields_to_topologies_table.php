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
        Schema::table('topologies', function (Blueprint $table) {
            $table->json('nodes')->nullable();
            $table->json('connections')->nullable();
            $table->float('power')->nullable();
            $table->text('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('topologies', function (Blueprint $table) {
            $table->dropColumn(['nodes', 'connections', 'power', 'description']);
        });
    }
};
