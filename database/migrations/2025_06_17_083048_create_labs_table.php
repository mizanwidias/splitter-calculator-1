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
        Schema::create('labs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('lab_group_id')->nullable();
            $table->foreign('lab_group_id')
                ->references('id')
                ->on('lab_groups')
                ->onDelete('set null');

            $table->string('name');              // Nama lab
            $table->string('slug');              // Slug lab (wajib buat nama file JSON unik per folder)

            $table->unique(['lab_group_id', 'slug']); // ✅ Slug unik per folder

            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labs');
    }
};
