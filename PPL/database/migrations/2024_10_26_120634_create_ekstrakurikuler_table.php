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
        Schema::disableForeignKeyConstraints();

        Schema::create('ekstrakurikuler', function (Blueprint $table) {
            $table->uuid('id_ekstrakurikuler')->primary();
            $table->uuid('guru_id');
            $table->foreign('guru_id')->references('id_guru')->on('guru');
            $table->string('nama_ekstrakurikuler');
            $table->text('deskripsi');
            $table->string('gambar');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekstrakurikuler');
    }
};
