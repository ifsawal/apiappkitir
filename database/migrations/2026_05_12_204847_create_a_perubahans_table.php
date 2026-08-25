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
        Schema::create('a_perubahans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tabel');
            $table->unsignedBigInteger('record_id');
            $table->string('aksi')->comment('tambah,ubah,hapus,edit');
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->string('status')->nullable()->comment('belum,diproses,selesai')->default('belum');
            $table->string('keterangan')->nullable();
            $table->integer('aproval_level')->nullable();
            $table->unsignedBigInteger('aproval_user_id')->nullable();
            $table->foreign('aproval_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->unsignedBigInteger('input_user_id');
            $table->foreign('input_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_perubahans');
    }
};
